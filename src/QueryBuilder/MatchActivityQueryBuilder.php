<?php

/**
 * @property BaseModel $type
 */
class MatchActivityQueryBuilder extends QueryBuilder
{
    protected function includeMatchActivity($selectColumns, $leftJoinOn)
    {
        $type = $this->type;
        $columns = $type::getEagerColumns($this->getFromAlias());

        $this->columns['activity'] = 'activity';
        $this->extraColumns = 'SUM(m2.activity) AS activity';
        $this->extras .= '
          LEFT JOIN
            (SELECT
              m.id,'
              . implode(',', $selectColumns) . ',
              TIMESTAMPDIFF(SECOND, timestamp, NOW()) / 86400 AS days_passed,
              (0.0116687059537612 * (POW((45 - LEAST((SELECT days_passed), 45)), (1/6)) + ATAN(31 - (SELECT days_passed)) / 2)) AS activity
            FROM
              matches m
            INNER JOIN
              match_participation mp ON m.id = mp.match_id
            WHERE
              DATEDIFF(NOW(), timestamp) <= 45
            ORDER BY
              timestamp DESC) m2 ON ' . $leftJoinOn
        ;

        $this->groupQuery = 'GROUP BY ' . $columns;

        return $this;
    }
}
