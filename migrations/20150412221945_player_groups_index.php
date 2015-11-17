<?php

use Phinx\Migration\AbstractMigration;

class PlayerGroupsIndex extends AbstractMigration
{
    /**
      * Change Method.
      *
      * More information on this method is available here:
      * http://docs.phinx.org/en/latest/migrations.html#the-change-method
      */
     public function change()
     {
         $this->table('player_groups')
            ->addIndex(array('player', 'group'), array('unique' => true))
            ->update();
     }
}
