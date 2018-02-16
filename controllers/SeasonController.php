<?php

use Pecee\Pixie\QueryBuilder\JoinBuilder;
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
        $qb = QueryBuilderFlex::createBuilder();
        $this->parseSeason($period, $year);

        // Subquery to get the the most recent Elo for players in the specified season
        $playerEloQuery = $qb->table('player_elo');
        $playerEloQuery
            ->select([
                'user_id',
                $qb->raw('MAX(match_id) AS last_match')
            ])
            ->where('season_period', '=', $period)
            ->where('season_year', '=', $year)
            ->groupBy('user_id')
        ;

        // Get Player models of the top 10 players
        $playersWithElos = Player::getQueryBuilder()
            ->select(
                $qb->raw('player_elo.elo_new AS elo')
            )
            ->innerJoin('player_elo', 'player_elo.user_id', '=', 'players.id')
            ->innerJoin(
                $qb->subQuery($playerEloQuery, 'i'),
                function ($table) {
                    /** @var JoinBuilder $table */
                    $table->on('i.user_id', '=', 'players.id');
                    $table->on('i.last_match', '=', 'player_elo.match_id');
                }
            )
            ->active()
            ->orderBy('elo', 'DESC')
            ->orderBy('players.username', 'ASC')
            ->limit(10)
            ->getModels()
        ;

        //
        // Get total amount of matches and their classification (fm or official)
        //

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

        //
        // Get map statistics; such as how many matches occurred in on each map
        //

        // Cache all of our non-deleted maps
        Map::getQueryBuilder()
            ->active()
            ->addToCache()
        ;

        $mapMatchCounts = QueryBuilderFlex::createForTable(Match::TABLE)
            ->where('timestamp', '>=', $seasonRange->getStartOfRange($year))
            ->where('timestamp', '<=', $seasonRange->getEndOfRange($year))
            ->whereNotNull('map')
            ->groupBy('map_id')
            ->having('match_count', '>', 0)
            ->orderBy('match_count', 'DESC')
            ->getArray([
                'map' => 'map_id',
                $qb->raw('COUNT(*) AS match_count'),
            ])
        ;

        $mapIDs = array_column($mapMatchCounts, 'map_id');
        $maps = Map::arrayIdToModel($mapIDs);
        $mapCount = array_combine($mapIDs, $mapMatchCounts);

        //
        // Get match count totals for players; how many official or fun matches a player participated in
        //

        $playerMatchTotals = QueryBuilderFlex::createForTable('match_participation')->alias('p');
        $playerMatchTotals
            ->select([
                'p.user_id',
                $qb->raw('COUNT(matches.id) AS match_count')
            ])
            ->innerJoin('matches', 'matches.id', '=', 'p.match_id')
            ->where('matches.timestamp', '>=', $seasonRange->getStartOfRange($year))
            ->where('matches.timestamp', '<', $seasonRange->getEndOfRange($year))
            ->groupBy('p.user_id')
            ->orderBy('match_count', 'DESC')
            ->limit(10)
        ;

        $fmResults   = (clone $playerMatchTotals)->where('matches.match_type', '=', Match::FUN)->get();
        $offiResults = (clone $playerMatchTotals)->where('matches.match_type', '=', Match::OFFICIAL)->get();

        // Get the unique player IDs from the above queries to cache them so we don't have individual queries for each
        // player.
        $matchTotalsPlayerIDs = array_unique(
            array_merge(
                array_column($fmResults, 'user_id'),
                array_column($offiResults, 'user_id')
            )
        );

        Player::getQueryBuilder()
            ->whereIn('id', $matchTotalsPlayerIDs)
            ->addToCache()
        ;

        return [
            'season'  => ucfirst($period),
            'year'    => $year,
            'players' => $playersWithElos,
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
