<?php
/**
 * This file contains functionality relating to the banned league players
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A ban imposed by an admin on a player
 * @package BZiON\Models
 */
class Ban extends Model
{
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
     * Either manually or automatically, whether or not a ban has expired
     * @var bool
     */
    private $expired;

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
     * Add an IP to the ban
     *
     * @param string $ipAddress The IP to add to a ban
     */
    public function addIP($ipAddress)
    {
        $this->db->query("INSERT INTO banned_ips (id, ban_id, ip_address) VALUES (NULL, ?, ?)", "is", array($this->getId(), $ipAddress));
    }

    /**
     * Check whether or not a player is allowed to join a server when they've been banned
     * @return bool Whether or not a player is allowed to join
     */
    public function allowedServerJoin()
    {
        return $this->allow_server_join;
    }

    /**
     * Get a literal value to whether or not a player can join the server
     * @return string "Yes" or "No" response
     */
    public function allowedServerJoinLiteral()
    {
        return ($this->allowedServerJoin()) ? "Yes" : "No";
    }

    /**
     * Get the user who imposed the ban
     * @return Player The banner
     */
    public function getAuthor()
    {
        return new Player($this->author);
    }

    /**
     * Get the creation time of the ban
     * @return string The creation time in a human readable form
     */
    public function getCreated()
    {
        return $this->created->diffForHumans();
    }

    /**
     * Get the expiration time of the ban
     * @return string The expiration time in a human readable form
     */
    public function getExpiration()
    {
        if ($this->hasExpired()) {
            return "Expired";
        }

        return $this->expiration->diffForHumans();
    }

    /**
     * Get the ban's description
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Get the ban summary that will appear when a player is denied access to a league server on join
     * @return string The ban summary
     */
    public function getServerMessage()
    {
        if ($this->allowedServerJoin()) {
            return "<em>No message available because the player is allowed to join servers to observe.</em>";
        }

        return $this->srvmsg;
    }

    /**
     * Get the IP address of the banned player
     * @return string
     */
    public function getIpAddresses()
    {
        return $this->ipAddresses;
    }

    /**
     * Get the time when the ban was last updated
     * @return string
     */
    public function getUpdated()
    {
        return $this->updated->diffForHumans();
    }

    /**
     * Get the player who was banned
     * @return Player The banned player
     */
    public function getVictim()
    {
        return new Player($this->player);
    }

    /**
     * Get the ID of the player who was banned
     * @return int The ID of the victim of the ban
     */
    public function getVictimID()
    {
        return $this->player;
    }

    /**
     * Calculate whether a ban has expired or not. If there is no need to calculate, then use isExpired() instead.
     *
     * @return bool True if the ban's expiration time has already passed
     */
    public function hasExpired()
    {
        if ($this->isExpired()) {
            return true;
        }

        return TimeDate::now()->gte($this->expiration);
    }

    /**
     * Check whether or not a ban has expired either manually or automatically
     *
     * @return bool Whether or not the ban has expired
     */
    public function isExpired()
    {
        return $this->expired;
    }

    /**
     * Unban a player
     */
    public function unban()
    {
        $this->update("expired", 1);
    }

    /**
     * Add a new ban
     *
     * @param int              $playerID        The ID of the victim of the ban
     * @param int              $authorID        The ID of the player responsible for the ban
     * @param string|\TimeDate $expiration      The expiration of the ban
     * @param string           $reason          The full reason for the ban
     * @param string           $srvmsg          A summary of the ban to be displayed on server banlists (max 150 characters)
     * @param string[]         $ipAddresses     An array of IPs that have been banned
     * @param bool             $allowServerJoin Whether or not
     *
     * @return Ban|bool An object representing the ban that was just entered or false if the ban was not created
     */
    public static function addBan($playerID, $authorID, $expiration, $reason, $srvmsg = "", $ipAddresses = array(), $allowServerJoin = false)
    {
        $db = Database::getInstance();
        $author = new Player($authorID);

        // Only add the ban if the author is valid and has the permission to add a ban
        if ($author->isValid() && $author->hasPermission(Permission::ADD_BAN)) {
            $player     = new Player($playerID);
            $expiration = new TimeDate($expiration);

            // Only ban valid players
            if ($player->isValid()) {
                $player->markAsBanned();

                // If there are no IPs to banned or no server ban message, then we'll allow the players to join as observers
                if (empty($srvmsg) || empty($ipAddresses)) {
                    $allowServerJoin = true;
                }

                $db->query(
                    "INSERT INTO bans (id, player, expiration, server_message, reason, allow_server_join, created, updated, author) VALUES (NULL, ?, ?, ?, ?, ?, NOW(), NOW(), ?)",
                    "isssii", array($playerID, $expiration->format(DATE_FORMAT), $srvmsg, $reason, $allowServerJoin, $authorID)
                );

                $ban = new Ban($db->getInsertId());

                if (is_array($ipAddresses)) {
                    foreach ($ipAddresses as $ip) {
                        $ban->addIP($ip);
                    }
                } else {
                    $ban->addIP($ipAddresses);
                }

                return $ban;
            }
        }

        return false;
    }

    /**
     * Get all the bans in the database that aren't disabled or deleted
     * @return Ban[] An array of ban objects
     */
    public static function getBans()
    {
        return self::arrayIdToModel(self::fetchIds("ORDER BY updated DESC"));
    }

}
