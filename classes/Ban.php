<?php

class Ban extends Controller {

    /**
     * The bzid of the banned player
     * @var int
     */
    private $player;

    /**
     * The ban expiration date
     * @var string
     */
    private $expiration;

    /**
     * The ban reason
     * @var string
     */
    private $reason;

    /**
     * The ban creation date
     * @var string
     */
    private $created;

    /**
     * The date the ban was last updated
     * @var string
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
        $ban = $this->result;

        $this->player = $ban['player'];
        $this->expiration = new DateTime($ban['expiration']);
        $this->reason = $ban['reason'];
        $this->created = new DateTime($ban['created']);
        $this->updated = new DateTime($ban['updated']);
        $this->author = $ban['author'];

    }

    function getPlayer() {
        return $this->player;
    }

    function getExpiration() {
        return $this->expiration->format(DATE_FORMAT);
    }

    function getReason() {
        return $this->reason;
    }

    function getCreated() {
        return $this->created->format(DATE_FORMAT);
    }

    function getUpdated() {
        return $this->updated->format(DATE_FORMAT);
    }

    function getAuthor() {
        return $this->author;
    }

    /**
     * Get all the bans in the database that arent disabled or deleted
     * @return array An array of ban IDs
     */
    public static function getBans($select = "id") {
        return parent::getIds();
    }

}
