<?php

class Player extends Controller
{

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
     * The access level of the player
     * @var int
     */
    private $access;

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
     * The player's timezone, in terms of distance from UTC (e.x. -5 for UTC-5)
     * @var int
     */
    private $timezone;

    /**
     * The date the player joined the site
     * @var string
     */
    private $joined;

    /**
     * The date of the player's last login
     * @var string
     */
    private $last_login;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "players";

    /**
     * Construct a new Player
     * @param int $bzid The player's bzid
     */
    function __construct($bzid) {

        parent::__construct($bzid, "bzid");
        $player = $this->result;

        $this->bzid = $bzid;
        $this->id = $player['id'];
        $this->username = $player['username'];
        $this->alias = $player['alias'];
        $this->team = $player['team'];
        $this->status = $player['status'];
        $this->access = $player['access'];
        $this->avatar = $player['avatar'];
        $this->description = $player['description'];
        $this->country = $player['country'];
        $this->timezone = $player['timezone'];
        $this->joined = new DateTime($player['joined']);
        $this->last_login = new DateTime($player['last_login']);

    }

    /**
     * Updates this player's last login
     * @param string $when The date of the last login
     */
    function updateLastLogin($when = "now") {
        $last = new DateTime($when);
        $results = $this->db->query("UPDATE players SET last_login = ? WHERE bzid = ?", "si", array($last->format(DATE_FORMAT), $this->bzid));
    }

    /**
     * Get the player's username
     * @return string The username
     */
    function getUsername() {
        return $this->username;
    }

    /**
     * Get the player's team
     * @return int The id of the team
     */
    function getTeam() {
        $team = new Team($this->team);
        return $team->getName();
    }

    /**
     * Get the joined date of the player
     * @return string The joined date of the player
     */
    function getJoinedDate() {
        return $this->joined->format(DATE_FORMAT);
    }

    /**
     * Enter a new player to the database
     * @param int $bzid The player's bzid
     * @param string $username The player's username
     * @param int $team The player's team
     * @param int $status The player's status
     * @param int $access The player's access level
     * @param string $avatar The player's profile avatar
     * @param string $description The player's profile description
     * @param int $country The player's country
     * @param int $timezone The player's timezone
     * @param date $joined The date the player joined
     * @param date $last_login The timestamp of the player's last login
     * @return Player An object representing the player that was just entered
     */
    public static function newPlayer($bzid, $username, $team=0, $status="active", $access=0, $avatar="", $description="", $country=0, $timezone=0, $joined="now", $last_login="now") {

        $db = Database::getInstance();

        $joined = new DateTime($joined);
        $last_login = new DateTime($last_login);

        $results = $db->query("INSERT INTO players (bzid, team, username, alias, status, access, avatar, description, country, timezone, joined, last_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        "iisssissiiss", array($bzid, $team, $username, Player::generateAlias($username), $status, $access, $avatar, $description, $country, $timezone, $joined->format(DATE_FORMAT), $last_login->format(DATE_FORMAT)));

        return new Player($bzid);
    }

    /**
     * Determine if a player exists in the database
     * @param int $bzid The player's bzid
     * @return bool Whether the player exists in the database
     */
    public static function playerExists($bzid) {
        $db = Database::getInstance();

        $results = $db->query("SELECT * FROM players WHERE bzid = ?", "i", array($bzid));

        return (count($results[0]) > 0);
    }

    /**
     * Get all the players in the database that have an active status
     * @return mixed An array of players
     */
    public static function getPlayers() {
        $db = Database::getInstance();

        $results = $db->query("SELECT bzid FROM players WHERE status=?", "s", array("active"));

        return $results;
    }

    /**
     * Get a URL that points to the player's page
     * @return string The URL
     */
    function getURL($dir="players", $default="bzid") {
        return parent::getURL($dir, $this->$default);
    }

    /*
     * Generate a URL-friendly unique alias for a username
     *
     * @param string $name The original username
     * @return string The generated alias
     */
    static function generateAlias($name) {
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
     * Gets a player object from the supplied alias
     * @param string $alias The player's alias
     * @return Player The player
     */
    public static function getFromAlias($alias) {
        return new Player(parent::getIdFrom($alias, "alias", true));
    }

}
