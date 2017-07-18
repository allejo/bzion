<?php

class PlayerQueryBuilder extends QueryBuilder
{
    public function withMatchActivity()
    {
        $type = $this->type;
        $columns = explode(',', $type::getEagerColumns());

        foreach ($columns as &$column) {
            $column = 'p.' . $column;
        }

        $this->tableAlias = 'p';
        $this->extraColumns = 'SUM(m2.activity) AS activity';
        $this->extras .= '
          LEFT JOIN
            (SELECT
              m.id,
              m.team_a_players,
              m.team_b_players,
              TIMESTAMPDIFF(SECOND, timestamp, NOW()) / 86400 AS days_passed,
              (0.0116687059537612 * (POW((45 - (SELECT days_passed)), (1/6)) + ATAN(31 - (SELECT days_passed)) / 2)) AS activity
            FROM
              matches m
            WHERE
              DATEDIFF(NOW(), timestamp) <= 45
            ORDER BY
              timestamp DESC) m2 ON FIND_IN_SET(p.id, m2.team_a_players) OR FIND_IN_SET(p.id, m2.team_b_players)
        ';
        $this->groupQuery = 'GROUP BY ' . implode(',', $columns);

        return $this;
    }
}
