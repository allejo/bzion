<?php

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
     * The bzid of the creator of the group
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

    function getSubject() {
        return $this->subject;
    }

    function getCreator() {
        return new Player($this->creator);
    }

    function isCreator($bzid) {
        return ($this->creator == $bzid);
    }

    function getLastActivity($human = true) {
        if ($human)
            return $this->last_activity->diffForHumans();
        else
            return $this->last_activity;
    }

    function getLastMessage() {
        $ids = self::getIdsFrom('group_to', array($this->id), 'i', false, 'id', 'ORDER BY id DESC LIMIT 0,1', 'messages');

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
     * Get a list containing the BZIDs of each member of the group
     * @param bool $hideSelf Whether to hide the currently logged in player
     * @return array An array of player BZIDs
     */
    function getMembers($hideSelf=false) {
        $additional_query = "WHERE `group` = ?";
        $types = "i";
        $params = array($this->id);

        if ($hideSelf && isset($_SESSION['bzid'])) {
            $additional_query .= " AND `player` != ?";
            $types .= "i";
            $params[] = $_SESSION['bzid'];
        }
        return parent::getIds("player", $additional_query, $types, $params, "player_groups");
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
        $additional_query = "LEFT JOIN groups ON player_groups.group=groups.id
                             WHERE player_groups.player = ? AND groups.status
                             NOT IN (?, ?) ORDER BY last_activity DESC";
        $params = array($bzid, "disabled", "deleted");

        return parent::getIds("groups.id", $additional_query, "iss", $params, "player_groups");
    }

    /**
     * Checks if a player belongs in the group
     * @param int $bzid The ID of the player
     * @return bool True if the player belongs in the group, false if they don't
     */
    public function isMember($bzid) {
        $result = $this->db->query("SELECT 1 FROM `player_groups` WHERE `group` = ?
                                    AND `player` = ?", "ii", array($this->id, $bzid));

        return count($result) > 0;
    }

    public static function hasNewMessage($bzid) {
        $groups = Group::getGroups($bzid);
        $me = new Player($bzid);

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
