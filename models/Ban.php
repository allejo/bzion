<?php

/**
 * A ban imposed by an admin on a player
 */
class Ban extends Model {

    /**
     * The id of the banned player
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
     * The id of the ban author
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
    public function __construct($id) {

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
     * @return Player The banned player
     */
    public function getPlayer() {
        return new Player($this->player);
    }

    /**
     * Get the IP address of the banned player
     * @return string
     */
    public function getIpAddress() {
        return $this->ipAddress;
    }

    /**
     * Get the expiration time of the ban
     * @return string The expiration time in a human readable form
     */
    public function getExpiration() {
        return $this->expiration->diffForHumans();
    }

    /**
     * Get the ban's description
     * @return string
     */
    public function getReason() {
        return $this->reason;
    }

    /**
     * Get the creation time of the ban
     * @return string The creation time in a human readable form
     */
    public function getCreated() {
        return $this->created->diffForHumans();
    }

    /**
     * Get the time when the ban was last updated
     * @return string
     */
    public function getUpdated() {
        return $this->updated->diffForHumans();
    }

    /**
     * Get the user who imposed the ban
     * @return Player The banner
     */
    public function getAuthor() {
        return new Player($this->author);
    }

    /**
     * Checks whether the ban has expired
     * @return boolean True if the ban's expiration time has already passed
     */
    public function hasExpired() {
        return TimeDate::now()->gte($this->expiration);
    }

    /**
     * Get all the bans in the database that aren't disabled or deleted
     * @return Ban[] An array of ban objects
     */
    public static function getBans() {
        return self::arrayIdToModel(self::fetchIds("ORDER BY updated DESC"));
    }

}
