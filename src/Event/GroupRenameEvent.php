<?php
/**
 * This file contains a group event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event thrown when a group gets renamed
 */
class GroupRenameEvent extends Event
{
    /**
     * @var \Group
     */
    protected $group;

    /**
     * @var string
     */
    protected $oldSubject;

    /**
     * @var string
     */
    protected $newSubject;

    /**
     * @var \Player
     */
    protected $player;

    /**
     * Create a new event
     *
     * @param \Group  $group   The group in question
     * @param string  $oldSubject The old name of the Group
     * @param string  $newSubject The new name of the group
     * @param \Player $player  The player who made the change
     */
    public function __construct(\Group $group, $oldSubject, $newSubject, \Player $player)
    {
        $this->group = $group;
        $this->oldSubject = $oldSubject;
        $this->newSubject = $newSubject;
        $this->player = $player;
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
     * Get the Player who renamed the group
     *
     * @return \Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Get the old name of the group
     *
     * @return string
     */
    public function getOldSubject()
    {
        return $this->oldSubject;
    }

    /**
     * Get the new name of the group
     *
     * @return string
     */
    public function getNewSubject()
    {
        return $this->newSubject;
    }
}
