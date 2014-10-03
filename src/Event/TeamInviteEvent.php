<?php
/**
 * This file contains a team event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event dispatched when someone invites a player to a team
 */
class TeamInviteEvent extends Event
{
    /**
     * @var \Invitation
     */
    protected $invitation;

    /**
     * Create a new event
     *
     * @param \Invitation $invitation The invitation
     */
    public function __construct(\Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the invitation
     *
     * @return \Invitation
     */
    public function getInvitation()
    {
        return $this->invitation;
    }

    /**
     * {@inheritDoc}
     */
    public function notify($type)
    {
        $this->doNotify($this->invitation->getInvitedPlayer(), $type);
    }
}
