<?php
/**
 * This file contains a group event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event announced when someone a player is kicked from a group
 */
class GroupKickEvent extends Event
{
    /**
     * @var \Group
     */
    protected $group;

    /**
     * @var \Player
     */
    protected $kicked;

    /**
     * @var \Player
     */
    protected $kicker;

    /**
     * Create a new event
     *
     * @param \Group  $group  The group from which the player was kicked
     * @param \Player $kicked The player who was kicked
     * @param \Player $kicker The player who issued the kick
     */
    public function __construct(\Group $group, \Player $kicked, \Player $kicker)
    {
        $this->group = $group;
        $this->kicked = $kicked;
        $this->kicker = $kicker;
    }

    /**
     * Get the group from which the player was kicked
     *
     * @return \Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get the player who was kicked
     *
     * @return \Player
     */
    public function getKicked()
    {
        return $this->kicked;
    }

    /**
     * Get the player who issued the kick
     *
     * @return \Player
     */
    public function getKicker()
    {
        return $this->kicker;
    }
}
