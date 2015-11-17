<?php
/**
 * This file contains a database migration
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Migration\AbstractMigration;
use BZIon\Migration\Delete;

class GroupTeamEvents extends AbstractMigration
{
    /**
     * Migration Method
     *
     * Changes in events:
     *  - GroupJoinEvent can now also store teams
     *  - GroupAbandonEvent and GroupKickEvent had the typehint of \Player
     *    converted to \Model to support both players and teams leaving groups;
     *    it is now necessary to store the type of the model in the appropriate
     *    format
     */
    public function up()
    {
        // Teams can now join groups
        $this->editConversationEvent('group.join', function (&$data) {
            if (!isset($data['teams'])) {
                $data['teams'] = array();
            }
        });

        // Teams can now get kicked from groups
        $this->editConversationEvent('group.kick', function (&$data) {
            $data['kicked'] = array(
                'id'   => $data['kicked'],
                'type' => 'Player'
            );
        });

        // Teams can now abandon groups
        $this->editConversationEvent('group.abandon', function (&$data) {
            $data['member'] = array(
                'id'   => $data['player'],
                'type' => 'Player'
            );
            unset($data['player']);
        });
    }

    /**
     * Rollback Method
     */
    public function down()
    {
        // No need to rollback the group.join event, team information can stay

        // Teams can't be kicked from groups
        $this->editConversationEvent('group.kick', function (&$data) {
            if ($data['kicked']['type'] === 'Player') {
                $data['kicked'] = $data['kicked']['id'];
            } else {
                // Delete events where a team gets kicked
                return new Delete();
            }
        });

        // Teams can't abandon groups
        $this->editConversationEvent('group.abandon', function (&$data) {
            if ($data['member']['type'] === 'Player') {
                $data['player'] = $data['member']['id'];
                unset($data['member']);
            } else {
                // Delete events where a team abandons the group
                return new Delete();
            }
        });
    }
}
