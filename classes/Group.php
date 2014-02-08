<?php

/**
 * A discussion (group of messages)
 */
class Group extends Model {

    /**
     * The subject of the group
     * @var string
     */
    private $subject;

    /**
     * The time of the last message to the group
     * @var TimeDate
     */
    private $last_activity;

    /**
     * The id of the creator of the group
     * @var int
     */
    private $creator;

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
     * Construct a new group
     * @param int $id The group's id
     */
    function __construct($id) {

        parent::__construct($id);
        if (!$this->valid) return;

        $group = $this->result;

        $this->subject = $group['subject'];
        $this->last_activity = TimeDate::parse($group['last_activity']);
        $this->creator = $group['creator'];
        $this->status = $group['status'];
    }

    /**
     * Get the subject of the discussion
     *
     * @return string
     **/
    function getSubject() {
        return $this->subject;
    }

    /**
     * Get the creator of the discussion
     *
     * @return Player
     */
    function getCreator() {
        return new Player($this->creator);
    }

    /**
     * Determine whether a player is the one who created the message group
     *
     * @param int $id The ID of the player to test for
     * @return bool
     */
    function isCreator($id) {
        return ($this->creator == $id);
    }

    /**
     * Get the time when the group was most recently active
     *
     * @param bool $human True to output the last activity in a human-readable string, false to return a TimeDate object
     * @return string|TimeDate
     */
    function getLastActivity($human = true) {
        if ($human)
            return $this->last_activity->diffForHumans();
        else
            return $this->last_activity;
    }

    /**
     * Get the last message of the group
     *
     * @return Message
     */
    function getLastMessage() {
        $ids = self::fetchIdsFrom('group_to', array($this->id), 'i', false, 'ORDER BY id DESC LIMIT 0,1', 'messages');

        return new Message($ids[0]);
    }

    /**
     * Get the URL that points to the group's page
     * @param string $dir The virtual directory the URL should point to
     * @param string $default The value that should be used if the alias is NULL. The object's ID will be used if a default value is not specified
     * @return string The group's URL, without a trailing slash
     */
    function getURL($dir="messages", $default=NULL) {
        return parent::getURL($dir, $default);
    }

    /**
     * Get a list containing the IDs of each member of the group
     * @param bool $hideSelf Whether to hide the currently logged in player
     * @return array An array of player IDs
     */
    function getMembers($hideSelf=false) {
        $additional_query = "WHERE `group` = ?";
        $types = "i";
        $params = array($this->id);

        if ($hideSelf && isset($_SESSION['playerId'])) {
            $additional_query .= " AND `player` != ?";
            $types .= "i";
            $params[] = $_SESSION['playerId'];
        }
        return parent::fetchIds($additional_query, $types, $params, "player_groups", "player");
    }

    /**
     * Create a new message group
     *
     * @param string $subject The subject of the group
     * @param array $members A list of IDs representing the group's members
     * @return Group An object that represents the created group
     */
    public static function createGroup($subject, $members=array())
    {
        $query = "INSERT INTO groups(subject, last_activity, status) VALUES(?, NOW(), ?)";
        $params = array($subject, "active");

        $db = Database::getInstance();
        $db->query($query, "ss", $params);
        $groupid = $db->getInsertId();

        foreach ($members as $mid) {
            $query = "INSERT INTO `player_groups` (`player`, `group`) VALUES(?, ?)";
            $db->query($query, "ii", array($mid, $groupid));
        }

        return new Group($groupid);
    }

    /**
     * Get all the groups in the database a player belongs to that are not disabled or deleted
     * @todo Move this to the Player class
     * @param int $id The id of the player whose groups are being retrieved
     * @return array An array of group IDs
     */
    public static function getGroups($id) {
        $additional_query = "LEFT JOIN groups ON player_groups.group=groups.id
                             WHERE player_groups.player = ? AND groups.status
                             NOT IN (?, ?) ORDER BY last_activity DESC";
        $params = array($id, "disabled", "deleted");

        return parent::fetchIds($additional_query, "iss", $params, "player_groups", "groups.id");
    }

    /**
     * Checks if a player belongs in the group
     * @param int $id The ID of the player
     * @return bool True if the player belongs in the group, false if they don't
     */
    public function isMember($id) {
        $result = $this->db->query("SELECT 1 FROM `player_groups` WHERE `group` = ?
                                    AND `player` = ?", "ii", array($this->id, $id));

        return count($result) > 0;
    }

    /**
     * Checks if a player has a new message in the group
     *
     * @todo Make this method work
     * @param int $id The ID of the player
     * @return boolean True if the player has a new message
     */
    public static function hasNewMessage($id) {
        $groups = Group::getGroups($id);
        $me = new Player($id);

        foreach ($groups as $key => $value) {
            $group = new Group($value);

            // THIS DOESNT WORK
            if ($me->getLastlogin(false)->gt($group->getLastActivity(false))) {
                return true;
            }
        }

        return false;
    }

}
