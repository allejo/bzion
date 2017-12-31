<?php

use Phinx\Migration\AbstractMigration;

/**
 * @link https://github.com/allejo/bzion/issues/156
 */
class NewMapOptionsIssue156 extends AbstractMigration
{
    public function change()
    {
        $mapsTable = $this->table('maps');
        $mapsTable
            ->addColumn('world_size', 'integer', [
                'after' => 'description',
                'signed' => false,
                'limit' => 5,
                'null' => true,
                'comment' => 'The world size of the map',
            ])
            ->addColumn('randomly_generated', 'boolean', [
                'after' => 'world_size',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the map is randomly generated each map',
            ])
            ->addColumn('game_mode', 'integer', [
                'after' => 'jumping',
                'signed' => false,
                'limit' => 2,
                'null' => false,
                'default' => 1,
                'comment' => 'The game mode this map follows', // see GAME_MODE_* consts in Map model
            ])
            ->update()
        ;
    }
}
