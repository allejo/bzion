<?php

class TeamQueryBuilder extends MatchActivityQueryBuilder
{
    public function withMatchActivity()
    {
        return $this->includeMatchActivity(
            ['m.team_a', 'm.team_b'],
            'teams.id = m2.team_a OR teams.id = m2.team_b'
        );
    }
}
