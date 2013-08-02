<?php

class Mail extends Controller
{

    /**
     * The BZID of the player the message was sent to
     * @var int
     */
    private $to;

    /**
     * The BZID of the player the message was sent from
     * @var int
     */
    private $from;

    /**
     * The subject of the message
     * @var string
     */
    private $subject;

    /**
     * The message creation date
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
     * Can be 'opened', 'unopened', 'deleted' or 'reported'
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "mail";

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
     * @param string $status The status of the message - can be 'opened', 'unopened', 'deleted' or 'reported'
     * @return Mail An object that represents the sent message
     */
    public static function sendMail($to, $from, $subject, $message, $status='unopened')
    {
        $query = "INSERT INTO mail VALUES(NULL, ?, ?, ?, NOW(), ?, ?)";
        $params = array($to, $from, $subject, $message, $status);

        $db = Database::getInstance();
        $db->query($query, "iisss", $params);

        return new Mail($db->getInsertId());
    }

}
