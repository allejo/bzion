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
class GroupEvent extends AbstractMessage implements GroupEventInterface
{
    /**
     * The event
     * @var Event
     */
    protected $event;

    /**
     * {@inheritDoc}
     */
    protected function assignResult($event)
    {
        parent::assignResult($event);

        $this->event = unserialize($event['message']);

        if ($this->isMessage()) {
            throw new Exception("A message cannot be represented by the GroupEvent class.");
        }
    }

    /**
     * Get the event object
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get the type of the event
     *
     * Do not use GroupEvent::getType(), as it returns the name of the class
     * (i.e. groupEvent)
     *
     * @return integer
     */
    public function getCategory()
    {
        return $this->type;
    }

    /**
     * Store a group event in the database
     *
     * @param  int        $group     The ID of the group
     * @param  Event      $event     The event
     * @param  mixed      $timestamp The timestamp when the event took place
     * @param  string     $status    The status of the event, can be 'visible', 'hidden', 'deleted' or 'reported'
     * @return GroupEvent
     */
    public static function storeEvent($group, $event, $type, $timestamp = 'now', $status = 'visible')
    {
        return self::create(array(
            "group_to"   => $group,
            "message"    => serialize($event),
            "event_type" => $type,
            "timestamp"  => TimeDate::from($timestamp)->toMysql(),
            "status"     => $status
        ), 'issss');
    }

    /**
     * Get a query builder for events
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return parent::getQueryBuilder()->eventsOnly();
    }
}
