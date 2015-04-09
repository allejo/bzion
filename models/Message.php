<?php
/**
 * This file contains functionality relating to all of actual messages sent by players
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A message between players or teams
 * @package    BZiON\Models
 */
class Message extends Model implements GroupEventInterface
{
    /**
     * The ID of the group this message belongs to
     * @var int
     */
    protected $group_to;

    /**
     * The ID of the player who sent the message
     * @var int
     */
    protected $player_from;

    /**
     * The timestamp of when the message was sent
     * @var TimeDate
     */
    protected $timestamp;

    /**
     * The content of the message
     * @var string
     */
    protected $message;

    /**
     * The status of the message
     *
     * Can be 'sent', 'hidden', 'deleted' or 'reported'
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
    protected function assignResult($message)
    {
        $this->group_to = $message['group_to'];
        $this->player_from = $message['player_from'];
        $this->timestamp = TimeDate::fromMysql($message['timestamp']);
        $this->message = $message['message'];
        $this->status = $message['status'];
    }

    /**
     * Get the content of the message
     * @return string The message itself
     */
    public function getContent()
    {
        return $this->message;
    }

    /**
     * Get the receiving group for the message
     * @return Group
     */
    public function getGroup()
    {
        return new Group($this->group_to);
    }

    /**
     * Gets the creator of the message
     * @return Player An object representing the message's author
     */
    public function getAuthor()
    {
        return new Player($this->player_from);
    }

    /**
     * Get the time when the message was sent
     * @return TimeDate
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Gets a human-readable representation of the time when the message was sent
     * @return string
     */
    public function getCreationDate()
    {
        return $this->timestamp->diffForHumans();
    }

    /**
     * Create a new message
     *
     * This method only stores a message in the database (doesn't update the
     * unread count or push live notifications), prefer to use Group::sendMessage()
     * instead.
     *
     * @param  int     $to      The id of the group the message is sent to
     * @param  int     $from    The ID of the sender
     * @param  string  $message The body of the message
     * @param  string  $status  The status of the message - can be 'sent', 'hidden', 'deleted' or 'reported'
     * @return Message An object that represents the sent message
     */
    public static function sendMessage($to, $from, $message, $status = 'sent')
    {
        return self::create(array(
            'group_to'    => $to,
            'player_from' => $from,
            'message'     => $message,
            'status'      => $status,
        ), 'iiss', 'timestamp');
    }

    /**
     * Get all the messages in the database that are not disabled or deleted
     * @param  int       $id The id of the group whose messages are being retrieved
     * @return Message[] An array of message IDs
     */
    public static function getMessages($id)
    {
        return self::arrayIdToModel(self::fetchIds("WHERE status NOT IN (?,?) AND group_to = ? ORDER BY timestamp ASC",
                              "ssi", array("hidden", "deleted", $id)));
    }

    /**
     * {@inheritDoc}
     */
    public static function getActiveStatuses()
    {
        return array('sent', 'reported');
    }

    /**
     * Get a query builder for messages
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new MessageQueryBuilder('Message', array(
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
    public function isMessage()
    {
        return true;
    }
}
