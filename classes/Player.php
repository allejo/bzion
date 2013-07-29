<?php

class Player {

    /**
     * The id of the player
     * @var int
     */
    private $id;

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
     * The database variable used for queries
     * @var Database
     */
    private $db;

    /**
     * Construct a new Player
     * @param int $bzid The player's bzid
     */
    function __construct($bzid) {

        $this->db = Database::getInstance();
        $this->bzid = $bzid;

        $results = $this->db->query("SELECT * FROM players WHERE bzid = ?", "i", array($bzid));
        $player = $results[0];

        $this->id = $player['id'];
        $this->username = $player['username'];
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
    public static function newPlayer($bzid, $username, $team=0, $status=0, $access=0, $avatar="", $description="", $country=0, $timezone=0, $joined="now", $last_login="now") {

        $joined = new DateTime($joined);
        $last_login = new DateTime($last_login);

        $results = $db->query("INSERT INTO players (bzid, team, username, status, access, avatar, description, country, timezone, joined, last_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        "iisiissiiss", array($bzid, $team, $username, $status, $access, $avatar, $description, $country, $timezone, $joined->format('Y-m-d H:i:s'), $last_login->format('Y-m-d H:i:s')));

        return new Player($db->getInsertId());
    }

    /**
     * Determine if a player exists in the database
     * @param int $bzid The player's bzid
     */
    public static function playerExists($bzid) {
        $results = $this->db->query("SELECT count(*) FROM players WHERE bzid = ?", "i", array($bzid));
        return ($results > 0);
    }

}
