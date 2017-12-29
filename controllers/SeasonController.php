<?php

use Symfony\Component\HttpFoundation\Request;

class SeasonController extends HTMLController
{
    public function showAction($season, Request $request)
    {
        $term = $year = '';
        $this->parseSeason($season, $term, $year);

        // Because this query can't be created efficiently using our QueryBuilder, let's do things manually
        $db = Database::getInstance();
        $seasonQuery = sprintf("
            SELECT %s, e.elo_new AS elo FROM players p 
              INNER JOIN player_elo e ON e.user_id = p.id 
              INNER JOIN (
                SELECT
                  user_id, 
                  MAX(match_id) AS last_match 
                FROM
                  player_elo 
                WHERE
                  season_period = ? AND season_year = ?
                GROUP BY
                  user_id
              ) i ON i.user_id = p.id AND i.last_match = e.match_id
            WHERE p.status = 'active'
            ORDER BY elo DESC, p.username ASC LIMIT 10;
        ", Player::getEagerColumns('p'));
        $results = $db->query($seasonQuery, [$term, $year]);
        $players_w_elos = Player::createFromDatabaseResults($results);

        $seasonRange = Season::getCurrentSeasonRange($term);
        $matchQuery = Match::getQueryBuilder();
        $matchQuery
            ->active()
            ->where('time')->isAfter($seasonRange->getStartOfRange($year), true)
            ->where('time')->isBefore($seasonRange->getEndOfRange($year), true)
        ;

        $fmQuery   = clone $matchQuery;
        $offiQuery = clone $matchQuery;

        $fmCount   = $fmQuery->where('type')->equals(Match::FUN)->count();
        $offiCount = $offiQuery->where('type')->equals(Match::OFFICIAL)->count();

        Map::getQueryBuilder()->addToCache();
        $mapQuery = '
            SELECT
              map AS map_id,
              COUNT(*) AS match_count
            FROM
              matches
            WHERE
              timestamp >= ? AND timestamp <= ? AND map IS NOT NULL
            GROUP BY
              map
            HAVING
              match_count > 0
            ORDER BY
              match_count DESC
        ';
        $results = $db->query($mapQuery, [
            $seasonRange->getStartOfRange($year),
            $seasonRange->getEndOfRange($year),
        ]);

        $mapIDs = array_column($results, 'map_id');
        $maps = Map::arrayIdToModel($mapIDs);
        $mapCount = array_combine($mapIDs, $results);

        $matchCount = "
            SELECT
              p.user_id,
              SUM(m.match_type = ?) AS match_count
            FROM
              match_participation p
            INNER JOIN
              matches m ON m.id = p.match_id
            WHERE
              m.timestamp >= ? AND m.timestamp < ?
            GROUP BY
              p.user_id
            ORDER BY
              match_count DESC
            LIMIT 10
        ";
        $fmResults = $db->query($matchCount, [
            'fm',
            $seasonRange->getStartOfRange($year),
            $seasonRange->getEndOfRange($year),
        ]);
        $offiResults = $db->query($matchCount, [
            'official',
            $seasonRange->getStartOfRange($year),
            $seasonRange->getEndOfRange($year),
        ]);

        return [
            'season'  => ucfirst($term),
            'year'    => $year,
            'players' => $players_w_elos,
            'fmCount' => $fmCount,
            'offiCount' => $offiCount,
            'maps'    => $maps,
            'mapCount' => $mapCount,
            'player_matches' => [
                'fm' => [
                    'players' => Player::arrayIdToModel(array_column($fmResults, 'user_id')),
                    'count' => array_column($fmResults, 'match_count'),
                ],
                'official' => [
                    'players' => Player::arrayIdToModel(array_column($offiResults, 'user_id')),
                    'count' => array_column($offiResults, 'match_count'),
                ],
            ],
        ];
    }

    private function parseSeason($string, &$term, &$year)
    {
        $string = strtolower($string);
        $currentSeason = ($string === 'current');

        if (!$currentSeason) {
            $seasonTerm = explode('-', $string);

            if ($this->validSeason($seasonTerm)) {
                $term = $seasonTerm[0];
                $year = (int)$seasonTerm[1];

                return;
            }
        }

        $term = Season::getCurrentSeason();
        $year = TimeDate::now()->year;

        return;
    }

    private function validSeason($seasonSplit)
    {
        if (empty($seasonSplit) || count($seasonSplit) != 2) {
            return false;
        }

        if (in_array($seasonSplit[0], [Season::WINTER, Season::SPRING, Season::SUMMER, Season::FALL])) {
            $currentYear = TimeDate::now()->year;
            $seasonYear = (int)$seasonSplit[1];

            // The season's in the future
            if ($seasonYear > $currentYear) {
                return false;
            }

            // If the year's the same, we need to make sure the season's not in the future; e.g. Fall 2017 shouldn't be
            // valid when it's only July 2017
            if ($seasonYear == $currentYear &&
                Season::toInt($seasonSplit[0]) > Season::toInt(Season::getCurrentSeason())
            ) {
                return false;
            }

            return true;
        }

        return false;
    }
}
