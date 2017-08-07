<?php

use Phinx\Migration\AbstractMigration;

class PlayerElo extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function change()
    {
        $matches = $this->table('matches');
        $matches
            ->addColumn('player_elo_diff', 'integer', [
                'after'   => 'elo_diff',
                'limit'   => 5,
                'signed'  => true,
                'null'    => true,
                'comment' => 'The change in ELO for players',
            ])
            ->update()
        ;

        $playerELOs = $this->table('player_elo', [
            'id' => false,
            'primary_key' => ['user_id', 'match_id']
        ]);
        $playerELOs
            ->addColumn('user_id', 'integer', [
                'limit'   => 10,
                'signed'  => false,
                'null'    => false,
                'comment' => 'The player whose had their individual ELO change',
            ])
            ->addColumn('match_id', 'integer', [
                'limit'   => 10,
                'signed'  => false,
                'null'    => false,
                'comment' => 'The match the player participated in, which resulted in this ELO change',
            ])
            ->addColumn('season_period', 'set', [
                'values'  => ['winter', 'spring', 'summer', 'fall'],
                'null'    => false,
                'comment' => 'The season this ELO change occurred in',
            ])
            ->addColumn('season_year', 'integer', [
                'limit'   => 4,
                'signed'  => false,
                'null'    => false,
                'comment' => 'The year of the season'
            ])
            ->addColumn('elo_previous', 'integer', [
                'signed'  => false,
                'null'    => false,
                'comment' => 'The ELO the player had prior to participating in this match',
            ])
            ->addColumn('elo_new', 'integer', [
                'signed'  => false,
                'null'    => false,
                'comment' => 'The new ELO for the player after participating in this match',
            ])
            ->addForeignKey('user_id', 'players', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('match_id', 'matches', 'id', ['delete' => 'CASCADE'])
            ->create()
        ;
    }
}
