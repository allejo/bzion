<?php
/**
 * This file contains a team event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event announced when someone a player is kicked from a team
 */
class TeamKickEvent extends Event
{
    /**
     * @var \Team
     */
    protected $team;

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
     * @param \Team   $team   The team from which the player was kicked
     * @param \Player $kicked The player who was kicked
     * @param \Player $kicker The player who issued the kick
     */
    public function __construct(\Team $team, \Player $kicked, \Player $kicker)
    {
        $this->team = $team;
        $this->kicked = $kicked;
        $this->kicker = $kicker;
    }

    /**
     * Get the team from which the player was kicked
     *
     * @return \Team
     */
    public function getTeam()
    {
        return $this->team;
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

    /**
     * {@inheritdoc}
     */
    public function notify($type)
    {
        $this->doNotify($this->kicked, $type);
    }
}
