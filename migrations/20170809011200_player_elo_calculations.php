<?php

use Phinx\Migration\AbstractMigration;

class PlayerEloCalculations extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $pageCountQuery = "
            SELECT
                COUNT(*)
            FROM
                matches
            WHERE
                team_a_players IS NOT NULL AND team_a_players != '' AND
                team_b_players IS NOT NULL AND team_b_players != '' AND 
                match_type = 'official' AND status = 'entered'
        ";
        $matchesQuery = "
            SELECT
                *
            FROM
                matches
            WHERE
                team_a_players IS NOT NULL AND team_a_players != '' AND
                team_b_players IS NOT NULL AND team_b_players != '' AND 
                match_type = 'official' AND status = 'entered' AND
                id > {id}
            ORDER BY
                `timestamp`
            LIMIT 1000
        ";

        $pageCount = ceil($this->fetchRow($pageCountQuery)[0] / 1000);
        $lastSeason = [];
        $lastID = 0;

        for ($i = 1; $i <= $pageCount; $i++) {
            $matches = $this->fetchAll(strtr($matchesQuery, [
                '{id}' => $lastID,
            ]));

            foreach ($matches as $match) {
                $timestamp = new \DateTime($match['timestamp']);
                $seasonInfo = [
                    'season' => $this->literalSeasonFromMonth($timestamp->format('n')),
                    'year' => (int)$timestamp->format('Y'),
                ];

                // Clear the model cache every season since Elos are cached internally
                if ($seasonInfo != $lastSeason) {
                    $lastSeason = $seasonInfo;
                }

                $teamA = explode(',', $match['team_a_players']);
                $teamB = explode(',', $match['team_b_players']);

                if (empty($teamA) || empty($teamB)) {
                    continue;
                }

                $getElo = function($n) use ($seasonInfo) {
                    $query = "
                        SELECT
                            elo_new
                        FROM
                            player_elo
                        WHERE
                            user_id = $n AND 
                            season_period = '{$seasonInfo['season']}' AND
                            season_year = {$seasonInfo['year']}
                        ORDER BY
                            match_id DESC
                        LIMIT 1
                    ";

                    $result = $this->fetchRow($query);

                    if (empty($result)) {
                        return 1200;
                    }

                    return $result[0];
                };

                $teamA_Avg = array_sum(array_map($getElo, $teamA)) / count($teamA);
                $teamB_Avg = array_sum(array_map($getElo, $teamB)) / count($teamB);

                $diff = self::calculateEloDiff(
                    $teamA_Avg, $teamB_Avg, $match['team_a_points'], $match['team_b_points'], $match['duration']
                );

                // We need to disable transactions so our Player::adjustElo() fxn won't hold up execution
                $this->getAdapter()->commitTransaction();

                $this->query("UPDATE matches SET player_elo_diff = {$diff} WHERE id = {$match['id']} LIMIT 1;");

                $walkFxn = function ($v, $k, $positive) use ($diff, $match, $seasonInfo, $getElo) {
                    $eloDiff = ($positive) ? $diff : -$diff;
                    $prevElo = $getElo($v);
                    $newElo = $prevElo + $eloDiff;
                    $query = "
                        INSERT INTO player_elo VALUES ($v, {$match['id']}, '{$seasonInfo['season']}', {$seasonInfo['year']}, $prevElo, $newElo)
                    ";

                    $this->execute($query);
                };

                array_walk($teamA, $walkFxn, true);
                array_walk($teamB, $walkFxn, false);

                $this->getAdapter()->beginTransaction();

                $lastSeason = $match['id'];
            }
        }
    }

    public function down()
    {
        $seasonElos = $this->table('player_elo');
        $seasonElos->truncate();
    }

    private function literalSeasonFromMonth($monthNumber)
    {
        $num = (int)$monthNumber;

        if (1 <= $num && $num <= 3) {
            return 'winter';
        } elseif (4 <= $num && $num <= 6) {
            return 'spring';
        } elseif (7 <= $num && $num <= 9) {
            return 'summer';
        }

        return 'fall';
    }

    private static function calculateEloDiff($a_elo, $b_elo, $a_points, $b_points, $duration)
    {
        $prob = 1.0 / (1 + pow(10, (($b_elo - $a_elo) / 400.0)));
        if ($a_points > $b_points) {
            $diff = 50 * (1 - $prob);
        } elseif ($a_points == $b_points) {
            $diff = 50 * (0.5 - $prob);
        } else {
            $diff = 50 * (0 - $prob);
        }

        // Apply ELO modifiers from `config.yml`
        $durations = [
            '30' => 3/3,
            '20' => 2/3,
            '15' => 1/3,
        ];
        $diff *= (isset($durations[$duration])) ? $durations[$duration] : 1;

        if (abs($diff) < 1 && $diff != 0) {
            // ELOs such as 0.75 should round up to 1...
            return ($diff > 0) ? 1 : -1;
        }

        // ...everything else is rounded down (-3.7 becomes -3 and 48.1 becomes 48)
        return intval($diff);
    }
}
