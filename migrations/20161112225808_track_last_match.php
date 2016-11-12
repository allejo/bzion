<?php

use Phinx\Migration\AbstractMigration;

class TrackLastMatch extends AbstractMigration
{
    public function up()
    {
        $playersTable = $this->table('players');
        $playersTable
            ->addColumn('last_match', 'integer', array(
                'signed' => false,
                'limit' => 10,
                'null' => true,
                'comment' => 'The timestamp of the last match this player participated in'
            ))
            ->addForeignKey('last_match', 'matches', 'id', array('delete' => 'SET_NULL'))
            ->update();

        $players = $this->fetchAll('SELECT id FROM players');

        foreach ($players as $player) {
            $this->query(
                "UPDATE players SET last_match = (
                    SELECT id FROM matches WHERE FIND_IN_SET(${player['id']}, team_a_players) OR FIND_IN_SET(${player['id']}, team_b_players) ORDER BY timestamp DESC LIMIT 1
                ) WHERE id = " . $player['id']
            );
        }
    }

    public function down()
    {
        $players = $this->table('players');
        $players
            ->dropForeignKey('last_match')
            ->removeColumn('last_match')
            ->update();
    }
}
