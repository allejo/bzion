<?php
/**
 * This file contains a group event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event dispatched whenever someone leaves a group
 */
class GroupAbandonEvent extends Event
{
    /**
     * @var \Group
     */
    protected $group;

    /**
     * @var \Player|\Team
     */
    protected $member;

    /**
     * Create a new event
     *
     * @param \Group        $group  The group that the player left
     * @param \Player|\Team $member The member who abandoned the group
     */
    public function __construct(\Group $group, \Model $member)
    {
        $this->group = $group;
        $this->member = $member;
    }

    /**
     * Get the group that the player abandoned
     *
     * @return \Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get the member who left the group
     *
     * @return \Player|\Team
     */
    public function getMember()
    {
        return $this->member;
    }
}
