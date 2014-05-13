<?php
/**
 * This file contains functionality relating to the banned league players
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

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
     * The ban expiration date
     * @var TimeDate
     */
    private $expiration;

    /**
     * The message that will appear when a player is denied connecting to a game server
     * @var string
     */
    private $srvmsg;

    /**
     * The ban reason
     * @var string
     */
    private $reason;

    /**
     * Whether or not a player is allowed to join a server when they are banned
     * @var bool
     */
    private $allow_server_join;

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
     * The IP of the banned player if the league would like to implement a global ban list
     * @var string[]
     */
    private $ipAddresses;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "bans";

    /**
     * Construct a new Ban
     * @param int $id The ban's id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        if (!$this->valid) return;

        $ban = $this->result;

        $this->player = $ban['player'];
        $this->expiration = new TimeDate($ban['expiration']);
        $this->srvmsg = $ban['server_message'];
        $this->reason = $ban['reason'];
        $this->allow_server_join = $ban['allow_server_join'];
        $this->created = new TimeDate($ban['created']);
        $this->updated = new TimeDate($ban['updated']);
        $this->author = $ban['author'];

        $this->ipAddresses = parent::fetchIds("WHERE ban_id = ?", 'i', array($this->getId()), "banned_ips", "ip_address");
    }

    /**
     * Check whether or not a player is allowed to join a server when they've been banned
     * @return bool Whether or not a player is allowed to join
     */
    public function allowedServerJoin() {
        return $this->allow_server_join;
    }

    /**
     * Get a literal value to whether or not a player can join the server
     * @return string "Yes" or "No" response
     */
    public function allowedServerJoinLiteral() {
        return ($this->allowedServerJoin()) ? "Yes" : "No";
    }

    /**
     * Get the user who imposed the ban
     * @return Player The banner
     */
    public function getAuthor() {
        return new Player($this->author);
    }

    /**
     * Get the creation time of the ban
     * @return string The creation time in a human readable form
     */
    public function getCreated() {
        return $this->created->diffForHumans();
    }

    /**
     * Get the expiration time of the ban
     * @return string The expiration time in a human readable form
     */
    public function getExpiration() {
        return $this->expiration->diffForHumans();
    }

    /**
     * Get the IP address of the banned player
     * @return string
     */
    public function getIpAddresses() {
        return $this->ipAddresses;
    }

    /**
     * Get the player who was banned
     * @return Player The banned player
     */
    public function getPlayer() {
        return new Player($this->player);
    }

    /**
     * Get the ban's description
     * @return string
     */
    public function getReason() {
        return $this->reason;
    }

    /**
     * Get the ban summary that will appear when a player is denied access to a league server on join
     * @return string The ban summary
     */
    public function getServerMessage()
    {
        if ($this->allowedServerJoin())
        {
            return "<em>No message available because the player is allowed to join servers to observe.</em>";
        }

        return $this->srvmsg;
    }

    /**
     * Get the time when the ban was last updated
     * @return string
     */
    public function getUpdated() {
        return $this->updated->diffForHumans();
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
