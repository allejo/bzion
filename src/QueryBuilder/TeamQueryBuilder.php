<?php

class TeamQueryBuilder extends QueryBuilder
{
    public function withMatchActivity()
    {
        /** @var BaseModel $type */
        $type = $this->type;
        $columns = $type::getEagerColumns($this->getFromAlias());

        $this->columns['activity'] = 'activity';
        $this->extraColumns = 'SUM(m2.activity) AS activity';
        $this->extras .= '
          LEFT JOIN
            (SELECT
              m.id,
              m.team_a,
              m.team_b,
              TIMESTAMPDIFF(SECOND, timestamp, NOW()) / 86400 AS days_passed,
              (0.0116687059537612 * (POW((45 - LEAST((SELECT days_passed), 45)), (1/6)) + ATAN(31 - (SELECT days_passed)) / 2)) AS activity
            FROM
              matches m
            WHERE
              DATEDIFF(NOW(), timestamp) <= 45 AND m.match_type = "official"
            ORDER BY
              timestamp DESC) m2 ON teams.id = m2.team_a OR teams.id = m2.team_b
        ';
        $this->groupQuery = 'GROUP BY ' . $columns;

        return $this;
    }
}
