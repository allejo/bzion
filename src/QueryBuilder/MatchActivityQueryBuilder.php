<?php

use Pecee\Pixie\QueryBuilder\JoinBuilder;

abstract class MatchActivityQueryBuilder extends QueryBuilderFlex
{
    /**
     * @throws \Pecee\Pixie\Exception
     * @throws Exception
     *
     * @return static
     */
    protected function getMatchActivityWorthQuery()
    {
        $qb = self::createBuilder();

        // The subquery to calculate each match's worth towards activity if it has occurred less than 45 days ago.
        //   - 86400 is in seconds; i.e. 24 hours
        //   - 0.0116687059537612 is a magic number
        $matchActivityWorthQuery = $qb->table('matches')->alias('m');
        $matchActivityWorthQuery
            ->select([
                'm.id',
                $qb->raw('TIMESTAMPDIFF(SECOND, `m`.`timestamp`, NOW()) / 86400 AS days_passed'),
                $qb->raw('(0.0116687059537612 * (POW((45 - LEAST((SELECT days_passed), 45)), (1/6)) + ATAN(31 - (SELECT days_passed)) / 2)) AS activity'),
            ])
            ->where($qb->raw('DATEDIFF(NOW(), `m`.`timestamp`) <= 45'))
            ->orderBy('m.timestamp', 'DESC')
        ;

        return $matchActivityWorthQuery;
    }

    /**
     * @throws \Pecee\Pixie\Exception
     * @throws Exception
     */
    protected function buildMatchActivity(QueryBuilderFlex $subQuery, JoinBuilder $joinBuilder)
    {
        $qb = self::createBuilder();
        $type = $this->modelType;

        $this
            ->select(
                $qb->raw('SUM(m2.activity) AS activity')
            )
            ->leftJoin(
                $qb->subQuery($subQuery, 'm2'),
                function (&$table) use ($joinBuilder) {
                    $table = $joinBuilder;
                }
            )
            ->groupBy($type::getEagerColumnsList())
        ;

        return $this;
    }
}
