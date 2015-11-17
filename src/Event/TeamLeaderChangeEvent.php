<?php
/**
 * This file contains a team event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event thrown when a player gets the leadership of a team
 */
class TeamLeaderChangeEvent extends Event
{
    /**
     * @var \Team
     */
    protected $team;

    /**
     * @var \Player
     */
    protected $newLeader;

    /**
     * @var \Player
     */
    protected $oldLeader;

    /**
     * Create a new event
     *
     * @param \Team   $team      The team in question
     * @param \Player $newLeader The new leader of the team
     * @param \Player $oldLeader The former leader of the team
     */
    public function __construct(\Team $team, \Player $newLeader, \Player $oldLeader)
    {
        $this->team = $team;
        $this->newLeader = $newLeader;
        $this->oldLeader = $oldLeader;
    }

    /**
     * Get the team
     *
     * @return \Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Get the new leader of the team
     *
     * @return \Player
     */
    public function getNewLeader()
    {
        return $this->newLeader;
    }

    /**
     * Get the former leader of the team
     *
     * @return \Player
     */
    public function getOldLeader()
    {
        return $this->oldLeader;
    }

    /**
     * {@inheritdoc}
     */
    public function notify($type)
    {
        $this->doNotify($this->newLeader, $type);
    }
}
