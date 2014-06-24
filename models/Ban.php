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
class Ban extends UrlModel implements PermissionModel
{
    /**
     * The id of the banned player
     * @var int
     */
    protected $player;

    /**
     * The ban expiration date
     * @var TimeDate
     */
    protected $expiration;

    /**
     * Either manually or automatically, whether or not a ban has expired
     * @var bool
     */
    protected $expired;

    /**
     * The message that will appear when a player is denied connecting to a game server
     * @var string
     */
    protected $srvmsg;

    /**
     * The ban reason
     * @var string
     */
    protected $reason;

    /**
     * Whether or not a player is allowed to join a server when they are banned
     * @var bool
     */
    protected $allowServerJoin;

    /**
     * The ban creation date
     * @var TimeDate
     */
    protected $created;

    /**
     * The date the ban was last updated
     * @var TimeDate
     */
    protected $updated;

    /**
     * The id of the ban author
     * @var int
     */
    protected $author;

    /**
     * The IP of the banned player if the league would like to implement a global ban list
     * @var string[]
     */
    protected $ipAddresses;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "bans";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($ban)
    {
        $this->player = $ban['player'];
        $this->expiration = new TimeDate($ban['expiration']);
        $this->srvmsg = $ban['server_message'];
        $this->reason = $ban['reason'];
        $this->allowServerJoin = $ban['allow_server_join'];
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
        $this->ipAddresses[] = $ipAddress;
        $this->db->query("INSERT INTO banned_ips (id, ban_id, ip_address) VALUES (NULL, ?, ?)", "is", array($this->getId(), $ipAddress));
    }

    /**
     * Remove an IP from the ban
     *
     * @param string $ipAddress The IP to remove from the ban
     */
    public function removeIP($ipAddress)
    {
        // Remove $ipAddress from $this->ipAddresses
        $this->ipAddresses = array_diff($this->ipAddresses, array($ipAddress));
        $this->db->query("DELETE FROM banned_ips WHERE ban_id = ? AND ip_address = ?", "is", array($this->getId(), $ipAddress));
    }

    /**
     * Set the IP addresses of the ban
     *
     * @todo   Is it worth making this faster?
     * @param  string[] $ipAddress The new IP addresses of the ban
     * @return self
     */
    public function setIPs($ipAddresses)
    {
        $oldIPs = $this->ipAddresses;
        $this->ipAddresses = $ipAddresses;

        $newIPs     = array_diff($ipAddresses, $oldIPs);
        $removedIPs = array_diff($oldIPs, $ipAddresses);

        foreach ($newIPs as $ip) {
            $this->addIP($ip);
        }

        foreach($removedIPs as $ip) {
            $this->removeIP($ip);
        }

        return $this;
    }

    /**
     * Check whether or not a player is allowed to join a server when they've been banned
     * @return bool Whether or not a player is allowed to join
     */
    public function allowedServerJoin()
    {
        return $this->allowServerJoin;
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
     * @return TimeDate
     */
    public function getExpiration()
    {
        return $this->expiration;
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
        if ($this->allowedServerJoin())
            return '';

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
     * Set the expiration date of the ban
     * @param mixed $expiration The expiration
     * @return self
     */
    public function setExpiration($expiration)
    {
        return $this->updateProperty($this->expiration, 'expiration', TimeDate::from($expiration), 's');
    }

    /**
     * Set the server message of the ban
     * @param string $message The new server message
     * @return self
     */
    public function setServerMessage($message)
    {
        return $this->updateProperty($this->srvmsg, 'server_message', $message, 's');
    }

    /**
     * Set the reason of the ban
     * @param string $reason The new ban reason
     * @return self
     */
    public function setReason($reason)
    {
        return $this->updateProperty($this->reason, 'reason', $reason, 's');
    }

    /**
     * Update the last edit timestamp
     * @return self
     */
    public function updateEditTimestamp()
    {
        return $this->updateProperty($this->updated, "updated", TimeDate::now(), 's');
    }

    /**
     * Set whether the ban's victim is allowed to enter a match server
     * @param boolean $allowServerJoin
     * @return self
     */
    public function setAllowServerJoin($allowServerJoin)
    {
        return $this->updateProperty($this->allowServerJoin, 'allow_server_join', (bool) $allowServerJoin);
    }

    /**
     * Unban a player
     * @return self
     */
    public function unban()
    {
        return $this->updateProperty($this->expired, 'expired', true);
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
        $author = new Player($authorID);

        // Only add the ban if the author is valid and has the permission to add a ban
        if ($author->isValid() && $author->hasPermission(Permission::ADD_BAN)) {
            $player     = new Player($playerID);
            $expiration = TimeDate::from($expiration);

            // Only ban valid players
            if ($player->isValid()) {
                $player->markAsBanned();

                // If there are no IPs to banned or no server ban message, then we'll allow the players to join as observers
                if (empty($srvmsg) || empty($ipAddresses)) {
                    $allowServerJoin = true;
                }

                $ban = self::create(array(
                    'player' => $playerID,
                    'expiration' => $expiration->toMysql(),
                    'server_message' => $srvmsg,
                    'reason' => $reason,
                    'allow_server_join' => $allowServerJoin,
                    'author' => $authorID,
                ), 'isssii', array('created', 'updated'));

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

    public static function getCreatePermission() { return Permission::ADD_BAN; }
    public static function getEditPermission() { return Permission::EDIT_BAN;  }
    public static function getSoftDeletePermission() { return Permission::SOFT_DELETE_BAN; }
    public static function getHardDeletePermission() { return Permission::HARD_DELETE_BAN; }

}
