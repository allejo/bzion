<?php
/**
 * This file contains functionality relating to events that happen in a group
 * except for Messages
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Event\Event;

/**
 * An event that happened in a group
 * @package    BZiON\Models
 */
class GroupEvent extends Model implements GroupEventInterface
{

    /**
     * The ID of the group where the event took place
     * @var int
     */
    protected $group;

    /**
     * The timestamp of when the message was sent
     * @var TimeDate
     */
    protected $timestamp;

    /**
     * The event
     * @var Event
     */
    protected $event;

    /**
     * The status of the event
     *
     * Can be 'visible' or 'deleted'
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "group_events";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($event)
    {
        $this->group = $event['group'];
        $this->event = unserialize($event['event']);
        $this->timestamp = new TimeDate($event['timestamp']);
        $this->status = $event['status'];
    }

    /**
     * Get the Event
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
    * Get the group where the event took place
    * @return Group
    */
    public function getGroup()
    {
        return new Group($this->group);
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
     * {@inheritDoc}
     */
    public static function getActiveStatuses()
    {
        return array('visible');
    }

    /**
     * Store a group event in the database
     *
     * @param  int $group The ID of the group
     * @param  Event $event The event
     * @param  mixed $timestamp The timestamp when the event took place
     * @param  string $status The status of the event
     * @return GroupEvent
     */
    public static function storeEvent($group, $event, $timestamp = 'now', $status = 'visible')
    {
        return self::create(array(
            "group"     => $group,
            "event"     => serialize($event),
            "timestamp" => TimeDate::from($timestamp)->toMysql(),
            "status"    => $status
        ), 'isss');
    }

    /**
     * Get a query builder for messages
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new QueryBuilder('GroupEvent', array(
            'columns' => array(
                'group'  => 'group',
                'time'   => 'timestamp',
                'status' => 'status'
            )
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function isMessage()
    {
        return false;
    }
}
