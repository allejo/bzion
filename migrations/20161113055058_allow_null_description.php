<?php

use Phinx\Migration\AbstractMigration;

class AllowNullDescription extends AbstractMigration
{
    public function up()
    {
        $players = $this->table('players');
        $players
            ->changeColumn('description', 'text', array(
                'null' => true,
                'comment' => 'The description or biography of a player'
            ));
    }
}
