<?php
/**
 * This file contains functionality relating to a league player
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A league player
 */
class Player extends AliasModel
{
    /**
     * These are built-in roles that cannot be deleted via the web interface so we will be storing these values as
     * constant variables. Hopefully, a user won't be silly enough to delete them manually from the database.
     */
    const DEVELOPER    = 1;
    const ADMIN        = 2;
    const COP          = 3;
    const REFEREE      = 4;
    const S_ADMIN      = 5;
    const PLAYER       = 6;
    const PLAYER_NO_PM = 7;

    /**
     * The bzid of the player
     * @var int
     */
    private $bzid;

    /**
     * The id of the player's team
     * @var int
     */
    private $team;

    /**
     * The username of the player
     * @var string
     */
    private $username;

    /**
     * The player's status
     * @var string
     */
    private $status;

    /**
     * The url of the player's profile avatar
     * @var string
     */
    private $avatar;

    /**
     * The player's profile description
     * @var string
     */
    private $description;

    /**
     * The id of the player's country
     * @var int
     */
    private $country;

    /**
     * The player's timezone, in terms of distance from UTC (i.e. -5 for UTC-5)
     * @var int
     */
    private $timezone;

    /**
     * The date the player joined the site
     * @var TimeDate
     */
    private $joined;

    /**
     * The date of the player's last login
     * @var TimeDate
     */
    private $last_login;

    /**
     * A section for admins to write notes about players
     * @var string
     */
    private $admin_notes;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "players";

    /**
     * Construct a new Player
     * @param int $id The player's ID
     */
    public function __construct($id) {

        parent::__construct($id);
        if (!$this->valid) return;

        $player = $this->result;

        $this->bzid = $player['bzid'];
        $this->username = $player['username'];
        $this->alias = $player['alias'];
        $this->team = $player['team'];
        $this->status = $player['status'];
        $this->avatar = $player['avatar'];
        $this->description = $player['description'];
        $this->country = $player['country'];
        $this->timezone = $player['timezone'];
        $this->joined = new TimeDate($player['joined']);
        $this->last_login = new TimeDate($player['last_login']);
        $this->admin_notes = $player['admin_notes'];

    }

    /**
     * Updates this player's last login
     * @param string $when The date of the last login
     */
    public function updateLastLogin($when = "now") {
        $last = new TimeDate($when);
        $this->db->query("UPDATE players SET last_login = ? WHERE id = ?", "si", array($last->format(DATE_FORMAT), $this->id));
    }

    /**
     * Get the player's username
     * @return string The username
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Get the player's BZID
     * @return int The BZID
     */
    public function getBZID() {
        return $this->bzid;
    }

    /**
     * Get the player's avatar
     * @return string The URL for the avatar
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * Set the player's avatar
     * @param string $avatar The URL for the avatar
     */
    public function setAvatar($avatar) {
        $this->db->query("UPDATE players SET avatar = ? WHERE id = ?", "si", array($avatar, $this->id));
    }

    /**
     * Get the player's sanitized description
     * @return string The description
     */
    public function getDescription() {
        return htmlspecialchars($this->description);
    }

    /**
     * Get the player's description, exactly as it is saved in the database
     * @return string The description
     */
    public function getRawDescription() {
        return $this->description;
    }

    /**
     * Set the player's description
     * @param string $description The description
     */
    public function setDescription($description) {
        $this->db->query("UPDATE players SET description = ? WHERE id = ?", "si", array($description, $this->id));
    }

    /**
     * Get the player's timezone
     * @return integer The timezone
     */
    public function getTimezone() {
        return $this->timezone;
    }

    /**
     * Set the player's timezone
     * @param string $timezone The timezone
     */
    public function setTimezone($timezone) {
        $this->db->query("UPDATE players SET timezone = ? WHERE id = ?", "si", array($timezone, $this->id));
    }

    /**
     * Get the notes admins have left about a player
     * @return string The notes
     */
    public function getAdminNotes() {
        return $this->admin_notes;
    }

    /**
     * Get the player's team
     * @return Team The object representing the team
     */
    public function getTeam() {
        return new Team($this->team);
    }

    /**
     * Get the joined date of the player
     * @return string The joined date of the player
     */
    public function getJoinedDate() {
        return $this->joined->diffForHumans();
    }

    /**
     * Get the last login for a player
     * @param bool $human Whether to get the literal time stamp or a relative time
     * @return string The date of the last login
     */
    public function getLastLogin($human = true) {
        if ($human)
            return $this->last_login->diffForHumans();
        else
            return $this->last_login;
    }

    /**
     * Generate the HTML for a hyperlink to link to a player's profile
     * @return string The HTML hyperlink to a player's profile
     */
    public function getLinkLiteral() {
        return '<a href="' . $this->getURL() . '">' . $this->getUsername() . '</a>';
    }

    /**
     * Get all of the callsigns a player has used to log in to the website
     * @return string[] An array containing all of the past callsigns recorded for a player
     */
    public function getPastCallsigns() {
        return parent::fetchIds("WHERE player = ?", "i", array($this->id), "past_callsigns", "username");
    }

    /**
     * Enter a new player to the database
     * @param int $bzid The player's bzid
     * @param string $username The player's username
     * @param int $team The player's team
     * @param string $status The player's status
     * @param int $role_id The player's role when they are first created
     * @param string $avatar The player's profile avatar
     * @param string $description The player's profile description
     * @param int $country The player's country
     * @param int $timezone The player's timezone
     * @param string|\TimeDate $joined The date the player joined
     * @param string|\TimeDate $last_login The timestamp of the player's last login
     * @return Player An object representing the player that was just entered
     */
    public static function newPlayer($bzid, $username, $team=0, $status="active", $role_id=self::PLAYER, $avatar="", $description="", $country=0, $timezone=0, $joined="now", $last_login="now")
    {
        $db = Database::getInstance();

        $joined = new TimeDate($joined);
        $last_login = new TimeDate($last_login);

        $db->query("INSERT INTO players (bzid, team, username, alias, status, avatar, description, country, timezone, joined, last_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        "iisssissiiss", array($bzid, $team, $username, self::generateAlias($username), $status, $avatar, $description, $country, $timezone, $joined->format(DATE_FORMAT), $last_login->format(DATE_FORMAT)));

        return new Player($db->getInsertId());
    }

    /**
     * Determine if a player exists in the database
     * @param int $bzid The player's bzid
     * @return bool Whether the player exists in the database
     */
    public static function playerBZIDExists($bzid) {
        return self::getFromBZID($bzid)->isValid();
    }

    /**
     * Given a player's BZID, get a player object
     *
     * @param int $bzid The player's BZID
     * @return Player
     */
    public static function getFromBZID($bzid) {
        return new Player(self::fetchIdFrom($bzid, "bzid", "s"));
    }

    /**
     * Get all the players in the database that have an active status
     * @return Player[] An array of player BZIDs
     */
    public static function getPlayers() {
        return self::arrayIdToModel(
            parent::fetchIdsFrom("status", array("active"), "s", false)
        );
    }

    /**
     * Generate a URL-friendly unique alias for a username
     * @param string $name The original username
     * @return string The generated alias
     */
    public static function generateAlias($name) {
        $name = strtolower($name);
        $name = str_replace(' ', '-', $name);

        // An alias name can't only contain numbers, because it will be
        // indistinguishable from an ID. If it does, add a dash in the end.
        if (preg_match("/^[0-9]+$/", $name)) {
            $name = $name . '-';
        }

        // Try to find duplicates
        $db = Database::getInstance();
        $result = $db->query("SELECT alias FROM players WHERE alias REGEXP ?", 's',
                  array("^". preg_quote($name) ."[0-9]*$"));

        // The functionality of the following code block is provided in PHP 5.5's
        // array_column function. What is does is convert the multi-dimensional
        // array that $db->query() gave us into a single-dimensional one.
        $aliases = array();
        if (is_array($result)) {
            foreach ($result as $r) {
                $aliases[] = $r['alias'];
            }
        }

        // No duplicates found
        if (!in_array($name, $aliases))
            return $name;

        // If there's already an entry with the alias we generated, put a number
        // in the end of it and keep incrementing it until there is we find
        // an open spot.
        $i = 2;
        while(in_array($name.$i, $aliases)) {
            $i++;
        }

        return $name.$i;
    }

    /**
     * Add a player's callsign to the database if it does not exist as a past callsign
     * @param string $id The ID for the player whose callsign we're saving
     * @param string $username The callsign which we are saving if it doesn't exist
     */
    public static function saveUsername($id, $username) {
        $db = Database::getInstance();

        $db->query("INSERT IGNORE INTO `past_callsigns` (id, player, username) VALUES (?, ?, ?)", "iis", array(NULL, $id, $username));
    }

    /**
     * Get all of the members belonging to a team
     * @param  int $teamID The ID of the team to fetch the members of
     * @return Player[] An array of Player objects of the team members
     */
    public static function getTeamMembers($teamID)
    {
        return self::arrayIdToModel(
            parent::fetchIds("WHERE team = ?", "i", array($teamID))
        );
    }
}
