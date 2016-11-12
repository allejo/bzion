<?php

use Phinx\Migration\AbstractMigration;

class DropPortColumn extends AbstractMigration
{
    public function up ()
    {
        $this->table('matches')
             ->removeColumn('port');
    }
}
