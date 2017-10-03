<?php

use Phinx\Migration\AbstractMigration;

class DropTeamActivity extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function change()
    {
        $table = $this->table('teams');
        $table
            ->removeColumn('activity')
            ->update()
        ;
    }
}
