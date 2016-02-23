<?php

/**
 * This file contains an abstract model class
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An abstraction for conversation events
 * @package    BZiON\Models
 */
abstract class AbstractMessage extends Model
{
    /**
     * The ID of the conversation where the event took place
     * @var int
     */
    protected $conversation;

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
     * {@inheritdoc}
     */
    protected function assignResult($event)
    {
        $this->conversation = $event['conversation_to'];
        $this->type = $event['event_type'];
        $this->timestamp = TimeDate::fromMysql($event['timestamp']);
        $this->status = $event['status'];
    }

    /**
     * Get the conversation where the event took place
     * @return Conversation
     */
    public function getConversation()
    {
        return Conversation::get($this->conversation);
    }

    /**
     * Get the time when the event occurred
     * @return TimeDate
     */
    public function getTimestamp()
    {
        return $this->timestamp->copy();
    }

    /**
     * Find out whether the event is a message and not a generic conversation event
     * (such as a rename or member join)
     *
     * @return bool
     */
    abstract public function isMessage();

    /**
     * {@inheritdoc}
     */
    public static function getActiveStatuses()
    {
        return array('visible', 'reported');
    }

    /**
     * {@inheritdoc}
     */
    public static function getQueryBuilder()
    {
        return new MessageQueryBuilder('AbstractMessage', array(
            'columns' => array(
                'conversation' => 'conversation_to',
                'time'         => 'timestamp',
                'status'       => 'status'
            )
        ));
    }

    /**
     * {@inheritdoc}
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
            return new ConversationEvent($id, $columns);
        }
    }
}
