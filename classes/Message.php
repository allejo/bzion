<?php

class Message extends Controller
{

    /**
     * The id of the group this message belongs to
     * @var int
     */
    private $group_to;

    /**
     * The BZID of the player who sent the message
     * @var int
     */
    private $player_from;

    /**
     * The timestamp of when the message was sent
     * @var string
     */
    private $timestamp;

    /**
     * The content of the message
     * @var string
     */
    private $message;

    /**
     * The status of the message
     *
     * Can be 'sent', 'hidden', 'deleted' or 'reported'
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "messages";

    /**
     * Construct a new message
     * @param int $id The message's id
     */
    function __construct($id) {

        parent::__construct($id);
        $message = $this->result;

        $this->to = $message['player_to'];
        $this->from = $message['player_from'];
        $this->subject = $message['subject'];
        $this->timestamp = $message['timestamp'];
        $this->message = $message['message'];
        $this->status = $message['status'];
    }

    /**
     * Create a new message
     *
     * @param int $to The BZID of the receiver
     * @param int $from The BZID of the sender
     * @param string $subject The subject of the message
     * @param string $message The body of the message
     * @param string $status The status of the message - can be 'sent', 'hidden', 'deleted' or 'reported'
     * @return Message An object that represents the sent message
     */
    public static function sendMessage($to, $from, $message, $status='sent')
    {
        $query = "INSERT INTO messages VALUES(NULL, ?, ?, NOW(), ?, ?)";
        $params = array($to, $from, $message, $status);

        $db = Database::getInstance();
        $db->query($query, "iiss", $params);

        return new Message($db->getInsertId());
    }

}
