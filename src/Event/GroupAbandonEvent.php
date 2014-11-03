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
     * @var \Player
     */
    protected $player;

    /**
     * Create a new event
     *
     * @param \Group  $group  The group that the player left
     * @param \Player $player The player who abandoned the group
     */
    public function __construct(\Group $group, \Player $player)
    {
        $this->group = $group;
        $this->player = $player;
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
     * Get the player who left the group
     *
     * @return \Player
     */
    public function getPlayer()
    {
        return $this->player;
    }
}
