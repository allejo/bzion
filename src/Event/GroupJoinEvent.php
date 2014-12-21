<?php
/**
 * This file contains a group event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event thrown when players join a group
 */
class GroupJoinEvent extends Event
{
    /**
     * @var \Group
     */
    protected $group;

    /**
     * @var \Player[]
     */
    protected $players;

    /**
     * Create a new event
     *
     * @param \Group    $group   The group in question
     * @param \Player[] $players The players who joined the group
     */
    public function __construct(\Group $group, array $players)
    {
        $this->group = $group;
        $this->players = $players;
    }

    /**
     * Get the group that was renamed
     *
     * @return \Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get the Players who joined the group
     *
     * @return \Player[]
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            'group'   => $this->group->getId(),
            'players' => \Player::mapToIDs($this->players)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $group = new \Group($data['group']);
        $players = \Player::arrayIdToModel($data['players']);

        $this->__construct($group, $players);
    }
}
