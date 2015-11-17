<?php

/**
 * This file contains an abstract model class
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An abstraction for group events
 * @package    BZiON\Models
 */
abstract class AbstractMessage extends Model implements GroupEventInterface
{
    /**
     * The ID of the group where the event took place
     * @var int
     */
    protected $group;

    /**
     * The timestamp of when the event took place
     * @var TimeDate
     */
    protected $timestamp;

    /**
     * The type of the event, or null if it's a message
     *
     * @var string|null
     */
    protected $type;

    /**
     * The status of the event
     *
     * Can be 'visible', 'hidden', 'deleted' or 'reported'
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "messages";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($event)
    {
        $this->group = $event['group_to'];
        $this->type = $event['event_type'];
        $this->timestamp = TimeDate::fromMysql($event['timestamp']);
        $this->status = $event['status'];
    }

    /**
     * Get the group where the event took place
     * @return Group
     */
    public function getGroup()
    {
        return Group::get($this->group);
    }

    /**
     * Get the time when the event occurred
     * @return TimeDate
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Find out whether the event is a message and not a generic group event
     * (such as a rename or member join)
     *
     * @return boolean
     */
    public function isMessage()
    {
        return $this->type === null;
    }

    /**
     * {@inheritDoc}
     */
    public static function getActiveStatuses()
    {
        return array('visible', 'reported');
    }

    /**
     * {@inheritDoc}
     */
    public static function getQueryBuilder()
    {
        return new MessageQueryBuilder('AbstractMessage', array(
            'columns' => array(
                'group'  => 'group_to',
                'time'   => 'timestamp',
                'status' => 'status'
            )
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected static function chooseModelFromDatabase($id)
    {
        $columns = static::fetchColumnValues($id);

        // Handle a non-existent model
        if ($columns === null) {
            if (get_called_class() === 'AbstractMessage') {
                // Default to returning an invalid Message
                return new Message($id, null);
            } else {
                return new static($id, null);
            }
        }

        // Determine whether the ID corresponds to a message or another event
        if ($columns['event_type'] === null) {
            return new Message($id, $columns);
        } else {
            return new GroupEvent($id, $columns);
        }
    }
}