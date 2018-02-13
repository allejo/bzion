<?php

use Pecee\Pixie\QueryBuilder\JoinBuilder;

class TeamQueryBuilder extends MatchActivityQueryBuilder
{
    /**
     * @throws \Pecee\Pixie\Exception
     * @throws Exception
     *
     * @return $this
     */
    public function withMatchActivity()
    {
        $subQuery = $this->getMatchActivityWorthQuery();
        $subQuery
            ->select([
                'm.team_a',
                'm.team_b',
            ])
        ;

        $join = new JoinBuilder();
        $join
            ->on('teams.id', '=', 'm2.team_a')
            ->orOn('teams.id', '=', 'm2.team_b')
        ;

        return $this->buildMatchActivity($subQuery, $join);
    }
}
