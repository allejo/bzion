<?php
/**
 * This file contains functionality relating to events that happen in a conversation
 * except for Messages
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Event\Event;

/**
 * An event that happened in a conversation
 * @package    BZiON\Models
 */
class ConversationEvent extends AbstractMessage
{
    /**
     * The event
     * @var Event
     */
    protected $event;

    /**
     * {@inheritdoc}
     */
    protected function assignResult($event)
    {
        parent::assignResult($event);

        if ($this->type === null) {
            throw new Exception("A message cannot be represented by the ConversationEvent class.");
        }

        $this->event = unserialize($event['message']);
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
     * Do not use ConversationEvent::getType(), as it returns the name of the class
     * (i.e. conversationEvent)
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function isMessage()
    {
        return false;
    }

    /**
     * Store a conversation event in the database
     *
     * @param  int        $conversation     The ID of the conversation
     * @param  Event      $event     The event
     * @param  string     $type      The type of the event
     * @param  mixed      $timestamp The timestamp when the event took place
     * @param  string     $status    The status of the event, can be 'visible', 'hidden', 'deleted' or 'reported'
     * @return ConversationEvent
     */
    public static function storeEvent($conversation, $event, $type, $timestamp = 'now', $status = 'visible')
    {
        return self::create(array(
            "conversation_to" => $conversation,
            "message"         => serialize($event),
            "event_type"      => $type,
            "timestamp"       => TimeDate::from($timestamp)->toMysql(),
            "status"          => $status
        ));
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
