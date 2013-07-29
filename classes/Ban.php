<?php

class Ban {

    /**
     * The id of the ban
     * @var int
     */
    private $id;

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
     * The database variable used for queries
     * @var Database
     */
    private $db;

    /**
     * Construct a new Ban
     * @param int $id The ban's id
     */
    function __construct($id) {

        $this->db = Database::getInstance();
        $this->id = $id;

        $results = $this->db->query("SELECT * FROM bans WHERE id = ?", "i", array($id));
        $ban = $results[0];

        $this->player = $ban['player'];
        $this->expiration = new DateTime($ban['expiration']);
        $this->reason = $ban['reason'];
        $this->created = unserialize($ban['created']);
        $this->updated = new DateTime($ban['updated']);
        $this->author = $ban['author'];

    }

    /**
     * Overload __set to update instance variables and database
     * @param string $name The variable's name
     * @param mixed $value The variable's new value
     */
    function __set($name, $value) {
        switch ($name) {
            case 'expiration':
                $this->db->query("UPDATE bans SET expiration = ? WHERE id = ?", "si", array($value, $this->id));
                $this->expiration = $value;
                break;
            case 'reason':
                $this->db->query("UPDATE bans SET reason = ? WHERE id = ?", "si", array($value, $this->id));
                $this->address = $value;
                break;
            case 'updated':
                $this->db->query("UPDATE bans SET updated = ? WHERE id = ?", "si", array($value, $this->id));
                $this->updated = $value;
                break;
            case 'author':
                $this->db->query("UPDATE bans SET info = ? WHERE id = ?", "si", array(serialize($value), $this->id));
                $this->author = $value;
                break;
        }
    }

}
