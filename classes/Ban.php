<?php

/**
 * A ban imposed by an admin on a player
 */
class Ban extends Model {

    /**
     * The bzid of the banned player
     * @var int
     */
    private $player;

    /**
     * The IP of the banned player if the league would like to implement a global ban list
     * @var string
     */
    private $ipAddress;

    /**
     * The ban expiration date
     * @var TimeDate
     */
    private $expiration;

    /**
     * The ban reason
     * @var string
     */
    private $reason;

    /**
     * The ban creation date
     * @var TimeDate
     */
    private $created;

    /**
     * The date the ban was last updated
     * @var TimeDate
     */
    private $updated;

    /**
     * The bzid of the ban author
     * @var int
     */
    private $author;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "bans";

    /**
     * Construct a new Ban
     * @param int $id The ban's id
     */
    function __construct($id) {

        parent::__construct($id);
        if (!$this->valid) return;

        $ban = $this->result;

        $this->player = $ban['player'];
        $this->ipAddress = $ban['ip_address'];
        $this->expiration = new TimeDate($ban['expiration']);
        $this->reason = $ban['reason'];
        $this->created = new TimeDate($ban['created']);
        $this->updated = new TimeDate($ban['updated']);
        $this->author = $ban['author'];

    }

    /**
     * Get the player who was banned
     * @return int The BZID of the banned player
     */
    function getPlayer() {
        return $this->player;
    }

    /**
     * Get the IP address of the banned player
     * @return string
     */
    function getIpAddress() {
        return $this->ipAddress;
    }

    /**
     * Get the expiration time of the ban
     * @return string The expiration time in a human readable form
     */
    function getExpiration() {
        return $this->expiration->diffForHumans();
    }

    /**
     * Get the ban's description
     * @return string
     */
    function getReason() {
        return $this->reason;
    }

    /**
     * Get the creation time of the ban
     * @return string The creation time in a human readable form
     */
    function getCreated() {
        return $this->created->diffForHumans();
    }

    /**
     * Get the time when the ban was last updated
     * @return string
     */
    function getUpdated() {
        return $this->updated->diffForHumans();
    }

    /**
     * Get the user who imposed the ban
     * @return int The BZID of the banner
     */
    function getAuthor() {
        return $this->author;
    }

    /**
     * Checks whether the ban has expired
     * @return boolean True if the ban's expiration time has already passed
     */
    function hasExpired() {
        return TimeDate::now()->gte($this->expiration);
    }

    /**
     * Get all the bans in the database that aren't disabled or deleted
     * @param string $select
     * @return array An array of ban IDs
     */
    public static function getBans($select = "id") {
        return parent::getIds($select, "ORDER BY updated DESC");
    }

}
