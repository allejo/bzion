<?php

use Pecee\Pixie\QueryBuilder\JoinBuilder;

class PlayerQueryBuilder extends MatchActivityQueryBuilder
{
    /**
     * @throws \Pecee\Pixie\Exception
     * @throws Exception
     */
    public function withMatchActivity()
    {
        $subQuery = $this->getMatchActivityWorthQuery();
        $subQuery
            ->select([
                'match_participation.user_id'
            ])
            ->innerJoin('match_participation', 'm.id', '=', 'match_participation.match_id')
        ;

        $join = new JoinBuilder();
        $join
            ->on('players.id', '=', 'm2.user_id')
        ;

        return $this->buildMatchActivity($subQuery, $join);
    }
}
