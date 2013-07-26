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

        $this->db = new Database();
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

}
