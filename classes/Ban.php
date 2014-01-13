<?php

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

    function getPlayer() {
        return $this->player;
    }

    function getIpAddress() {
        return $this->ipAddress;
    }

    function getExpiration() {
        return $this->expiration->diffForHumans();
    }

    function getReason() {
        return $this->reason;
    }

    function getCreated() {
        return $this->created->diffForHumans();
    }

    function getUpdated() {
        return $this->updated->diffForHumans();
    }

    function getAuthor() {
        return $this->author;
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
