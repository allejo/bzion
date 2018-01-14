<?php

use Phinx\Migration\AbstractMigration;

class AddPlayerColorBlindMode extends AbstractMigration
{
    public function change()
    {
        $playersTable = $this->table('players');
        $playersTable
            ->addColumn('color_blind_enabled', 'boolean', [
                'after' => 'theme',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the player has opted for color blind assistance',
            ])
            ->update()
        ;
    }
}
