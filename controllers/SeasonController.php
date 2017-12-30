<?php

use Symfony\Component\HttpFoundation\Request;

class SeasonController extends HTMLController
{
    /**
     * @param Request $request
     * @param string  $period    The season's period: winter, spring, summer, fall
     * @param int     $year      The season's year
     *
     * @throws Exception         When a database instance is not configured for the current environment
     * @throws NotFoundException When an invalid season is given
     *
     * @return array
     */
    public function showAction(Request $request, $period, $year)
    {
        $this->parseSeason($period, $year);

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
        $results = $db->query($seasonQuery, [$period, $year]);
        $players_w_elos = Player::createFromDatabaseResults($results);

        $seasonRange = Season::getCurrentSeasonRange($period);
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
            'season'  => ucfirst($period),
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

    /**
     * Default to current season or ensure the given season is valid.
     *
     * @param string $period
     * @param int    $year
     *
     * @throws NotFoundException When an invalid season is found
     */
    private function parseSeason(&$period, &$year)
    {
        if (!$this->isValidSeason($period, $year)) {
            throw new NotFoundException('The specified season does not seem to exist.');
        }

        if ($period === 'current') {
            $period = Season::getCurrentSeason();
            $year = TimeDate::now()->year;
        }
    }

    /**
     * Check that a given season is valid.
     *
     * @param string $period
     * @param int    $seasonYear
     *
     * @return bool
     */
    private function isValidSeason($period, $seasonYear)
    {
        $currentYear = TimeDate::now()->year;

        // The season's in the future
        if ($seasonYear > $currentYear) {
            return false;
        }

        // If the year's the same, we need to make sure the season's not in the future; e.g. Fall 2017 shouldn't be
        // valid when it's only July 2017
        if ($seasonYear == $currentYear &&
            Season::toInt($period) > Season::toInt(Season::getCurrentSeason())
        ) {
            return false;
        }

        return true;
    }
}
