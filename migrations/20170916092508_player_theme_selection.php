<?php

use Phinx\Migration\AbstractMigration;

class PlayerThemeSelection extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function change()
    {
        $table = $this->table('players');
        $table
            ->addColumn('theme', 'string', [
                'after'   => 'timezone',
                'default' => 'dark',
                'comment' => 'The color theme the player has choosen'
            ])
            ->update()
        ;
    }
}
