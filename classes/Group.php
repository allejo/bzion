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
     * Create a new message group
     *
     * @param string $subject The subject of the group
     * @param array $members A list of BZIDs representing the group's members
     * @return Group An object that represents the created group
     */
    public static function createGroup($subject, $members=array())
    {
        $query = "INSERT INTO groups(subject, last_activity, status) VALUES(?, NOW(), ?)";
        $params = array($subject, "active");

        $db = Database::getInstance();
        $db->query($query, "ss", $params);
        $groupid = $db->getInsertId();

        foreach ($members as $bzid) {
            $query = "INSERT INTO `player_groups` (`player`, `group`) VALUES(?, ?)";
            $db->query($query, "ii", array($bzid, $groupid));
        }

        return new Group($groupid);
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
