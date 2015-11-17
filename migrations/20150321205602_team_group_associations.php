<?php
/**
 * This file contains a database migration
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Migration\AbstractMigration;

class TeamGroupAssociations extends AbstractMigration
{
    /**
      * Change Method.
      *
      * More information on this method is available here:
      * http://docs.phinx.org/en/latest/migrations.html#the-change-method
      */
     public function change()
     {
         $teamGroup = $this->table('team_groups', array('id' => false, 'primary_key' => 'id'));
         $teamGroup->addColumn('id', 'integer', array('limit' => 10, 'signed' => false, 'identity' => true))
                   ->addColumn('team', 'integer', array('limit' => 10, 'signed' => false, 'comment' => 'The team ID'))
                   ->addColumn('group', 'integer', array('limit' => 10, 'signed' => false, 'comment' => 'The group ID a team belongs to'))
                   ->addForeignKey('group', 'groups', 'id', array('delete' => 'CASCADE'))
                   ->addForeignKey('team', 'teams', 'id', array('delete' => 'CASCADE'))
                   ->addIndex(array('team', 'group'), array('unique' => true))
                   ->create();

         $playerGroup = $this->table('player_groups');
         $playerGroup->addColumn('distinct', 'boolean', array(
            'default' => true,
            'comment' => '1 if the player was specifically invited to the conversation, 0 if the player is participating only as a member of a team'
        ));
         $playerGroup->update();
     }
}
