<?php

class Group extends Controller {

    /**
     * The subject of the group
     * @var string
     */
    private $subject;

    /**
     * The timestamp of the last message to the group
     * @var string
     */
    private $last_activity;

    /**
     * The status of the group
     *
     * Can be 'active', 'disabled', 'deleted' or 'reported'
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "groups";

    /**
     * Construct a new message
     * @param int $id The message's id
     */
    function __construct($id) {

        parent::__construct($id);
        $group = $this->result;

        $this->subject = $group['subject'];
        $this->last_activity = new DateTime($group['last_activity']);
        $this->status = $group['status'];
    }

    function getSubject() {
        return $this->subject;
    }

    function getLastActivity() {
        $last_message = $this->last_activity->diff(new DateTime("now"));

        if ($last_message->y + $last_message->m + $last_message->d + $last_message->h + $last_message->i == 0) {
            if ($last_message->s < 10) {
                return "now";
            } else {
                return $last_message->format('%s sec ago');
            }
        } elseif ($last_message->y + $last_message->m + $last_message->d + $last_message->h == 0) {
            return $last_message->format('%i min ago');
        } elseif ($last_message->y + $last_message->m + $last_message->d == 0) {
            return $last_message->format('%h hour(s) ago');
        } else {
            return $last_message->format('%d day(s) ago');
        }

        //return $this->last_activity->format(DATE_FORMAT);
    }

    /**
     * Get the URL that points to the group's page
     * @return string The group's URL, without a trailing slash
     */
    function getURL($dir="messages", $default=NULL) {
        return parent::getURL($dir, $default);
    }

    /**
     * Create a new message
     *
     * @param int $to The id of the group the message is sent to
     * @param int $from The BZID of the sender
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

    /**
     * Get all the groups in the database a player belongs to that are not disabled or deleted
     * @param int $bzid The bzid of the player whose groups are being retrieved
     * @return array An array of group IDs
     */
    public static function getGroups($bzid) {
        return parent::getIdsFrom("player", array($bzid), "i", false, "id", "", "player_groups");
    }

}
