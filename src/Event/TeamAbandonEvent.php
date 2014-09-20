<?php
/**
 * This file contains a team event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event dispatched whenever someone leaves a team
 */
class TeamAbandonEvent extends Event {
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
     * @param \Team $team The team that the player left
     * @param \Player $player The player who abandoned the team
     */
    public function __construct(\Team $team, \Player $player)
    {
        $this->team = $team;
        $this->player = $player;
    }

    /**
     * Get the team that the player abandoned
     *
     * @return \Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Get the player who left the team
     *
     * @return \Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * {@inheritDoc}
     */
    public function notify($type)
    {
        $this->doNotify($this->team->getLeader(), $type);
    }
}
