<?php
/**
 * This file contains a team event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event announced when a team is deleted
 */
class TeamDeleteEvent extends Event
{
    /**
     * @var \Team
     */
    protected $team;

    /**
     * @var \Player
     */
    protected $deleter;

    /**
     * @var null|\Player[]
     */
    protected $members = null;

    /**
     * Create a new event
     *
     * @param \Team     $team    The team that was deleted
     * @param \Player   $deleter The player who deleted the team
     * @param \Player[] $members The members of the deleted team
     */
    public function __construct(\Team $team, \Player $deleter, array $members = null)
    {
        $this->team = $team;
        $this->deleter = $deleter;
        $this->members = $members;
    }

    /**
     * Get the team that was deleted
     *
     * @return \Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Get the player who deleted the team
     *
     * @return \Player
     */
    public function getDeleter()
    {
        return $this->deleter;
    }

    /**
     * Get the former members of the team
     *
     * @return null|\Player[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * {@inheritDoc}
     */
    public function notify($type)
    {
        $this->doNotify($this->members, $type, $this->deleter);
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            'team'    => $this->team->getId(),
            'deleter' => $this->deleter->getId()
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->__construct(\Team::get($data['team']), \Player::get($data['deleter']));
    }
}
