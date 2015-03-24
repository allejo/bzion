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
     * @var \Model[]
     */
    protected $members;

    /**
     * Create a new event
     *
     * @param \Group   $group   The group in question
     * @param \Model[] $members The players and teams who joined the group
     */
    public function __construct(\Group $group, array $members)
    {
        $this->group  = $group;
        $this->members = $members;
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
     * Get the Players and Teams who joined the group
     *
     * @return \Model[]
     */
    public function getNewMembers()
    {
        return $this->members;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        $players = $teams = array();

        foreach ($this->members as $member) {
            if ($member instanceof \Player) {
                $players[] = $member;
            } else {
                $teams[] = $member;
            }
        }

        return serialize(array(
            'group'   => $this->group->getId(),
            'players' => \Player::mapToIDs($players),
            'teams' => \Team::mapToIDs($teams)
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
        $teams = \Team::arrayIdToModel($data['teams']);

        $this->__construct($group, array_merge($players, $teams));
    }
}
