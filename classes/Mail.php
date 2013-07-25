<?php

class Mail {

    /**
     * The id of the message
     * @var int
     */
    private $id;
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
     * The database variable used for queries
     * @var MySQLi
     */
    private $db;

    /**
     * Construct a new message
     * @param int $id The message's id
     */
    function __construct($id) {

        $this->db = new Database();
        $this->id = $id;

        $results = $this->db->query("SELECT * FROM mail WHERE id = ?", "i", array($id));
	$message = $results[0];

        $this->to = $message['player_to'];
        $this->from = $message['player_from'];
        $this->subject = $message['subject'];
        $this->timestamp = $message['timestamp'];
        $this->message = $message['message'];
        $this->status = $message['status'];
    }

    /**
     * Overload __set to update instance variables and database
     * @param string $name The variable's name
     * @param mixed $value The variable's new value
     */
    function __set($name, $value) {
        $table = "mail";

        if ($name == 'to' || $name == 'from') {
	    $this->db->query("UPDATE ". $table . " SET player_" . $name . " = ? WHERE id = ?", "ii", array($value, $this->id));
	    $this->{$name} = $value;
	} else if ($name == 'subject' || $name == 'timestamp' || $name == 'message' || $name == 'status') {
	    $this->db->query("UPDATE ". $table . " SET " . $name . " = ? WHERE id = ?", "si", array($value, $this->id));
	    $this->{$name} = $value;
	}
    }

    /**
     * Delete the message
     *
     * Please note that this does not delete the message entirely from the database,
     * it only hides it from users.
     */
    function delete() {
        $this->__set('status', 'deleted');
    }

    /**
     * Permanently delete the message
     *
     * This function deletes the message from the database, making it impossible to recover.
     */
    function wipe() {
        $this->db->query("DELETE FROM mail WHERE id = ?", "i", array($this->id));
    }

}
