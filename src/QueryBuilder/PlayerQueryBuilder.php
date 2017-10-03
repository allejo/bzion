<?php

class PlayerQueryBuilder extends MatchActivityQueryBuilder
{
    public function withMatchActivity()
    {
        return $this->includeMatchActivity(
            ['m.team_a_players', 'm.team_b_players'],
            'FIND_IN_SET(players.id, m2.team_a_players) OR FIND_IN_SET(players.id, m2.team_b_players)'
        );
    }
}
