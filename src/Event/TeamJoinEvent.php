<?php
/**
 * This file contains a team event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event dispatched whenever someone joins a team
 */
class TeamJoinEvent extends Event
{
    /**
     * @var \Team
     */
    protected $team;

    /**
     * @var \Player
     */
    protected $player;

    /**
     * Create a new event
     *
     * @param \Team   $team   The team that the player joined
     * @param \Player $player The player who joined the team
     */
    public function __construct(\Team $team, \Player $player)
    {
        $this->team = $team;
        $this->player = $player;
    }

    /**
     * Get the team that the player joined
     *
     * @return \Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Get the player who joined the team
     *
     * @return \Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * {@inheritdoc}
     */
    public function notify($type)
    {
        $this->doNotify($this->team->getLeader(), $type);
    }
}
