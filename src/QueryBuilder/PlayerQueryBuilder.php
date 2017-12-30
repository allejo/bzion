<?php

class PlayerQueryBuilder extends MatchActivityQueryBuilder
{
    public function withMatchActivity()
    {
        return $this->includeMatchActivity(
            ['mp.user_id'],
            'players.id = m2.user_id'
        );
    }
}
