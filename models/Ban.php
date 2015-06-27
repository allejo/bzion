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
class Ban extends UrlModel implements NamedModel
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
     * The ban's status
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "bans";

    const CREATE_PERMISSION = Permission::ADD_BAN;
    const EDIT_PERMISSION = Permission::EDIT_BAN;
    const SOFT_DELETE_PERMISSION = Permission::SOFT_DELETE_BAN;
    const HARD_DELETE_PERMISSION = Permission::HARD_DELETE_BAN;

    /**
     * {@inheritDoc}
     */
    protected function assignResult($ban)
    {
        $this->player = $ban['player'];
        $this->expiration = ($ban['expiration'] === null)
                          ? null
                          : TimeDate::fromMysql($ban['expiration']);
        $this->srvmsg = $ban['server_message'];
        $this->reason = $ban['reason'];
        $this->allowServerJoin = $ban['allow_server_join'];
        $this->created = TimeDate::fromMysql($ban['created']);
        $this->updated = TimeDate::fromMysql($ban['updated']);
        $this->author = $ban['author'];
        $this->status = $ban['status'];
    }

    /**
     * {@inheritDoc}
     */
    protected function assignLazyResult($result)
    {
        $this->ipAddresses = parent::fetchIds("WHERE ban_id = ?", 'i', array($this->getId()), "banned_ips", "ip_address");
    }

    /**
     * Add an IP to the ban
     *
     * @param string $ipAddress The IP to add to a ban
     */
    public function addIP($ipAddress)
    {
        $this->lazyLoad();

        $this->ipAddresses[] = $ipAddress;
        $this->db->query("INSERT IGNORE INTO banned_ips (id, ban_id, ip_address) VALUES (NULL, ?, ?)", "is", array($this->getId(), $ipAddress));
    }

    /**
     * Remove an IP from the ban
     *
     * @param string $ipAddress The IP to remove from the ban
     */
    public function removeIP($ipAddress)
    {
        $this->lazyLoad();

        // Remove $ipAddress from $this->ipAddresses
        $this->ipAddresses = array_diff($this->ipAddresses, array($ipAddress));
        $this->db->query("DELETE FROM banned_ips WHERE ban_id = ? AND ip_address = ?", "is", array($this->getId(), $ipAddress));
    }

    /**
     * Set the IP addresses of the ban
     *
     * @todo   Is it worth making this faster?
     * @param  string[] $ipAddresses The new IP addresses of the ban
     * @return self
     */
    public function setIPs($ipAddresses)
    {
        $this->lazyLoad();

        $oldIPs = $this->ipAddresses;
        $this->ipAddresses = $ipAddresses;

        $newIPs     = array_diff($ipAddresses, $oldIPs);
        $removedIPs = array_diff($oldIPs, $ipAddresses);

        foreach ($newIPs as $ip) {
            $this->addIP($ip);
        }

        foreach ($removedIPs as $ip) {
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
        return Player::get($this->author);
    }

    /**
     * Get the creation time of the ban
     * @return TimeDate The creation time
     */
    public function getCreated()
    {
        return $this->created;
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
        if ($this->allowedServerJoin()) {
            return '';
        }

        return $this->srvmsg;
    }

    /**
     * Get the IP address of the banned player
     * @return string[]
     */
    public function getIpAddresses()
    {
        $this->lazyLoad();

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
        return Player::get($this->player);
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
     * Calculate whether a ban has expired or not.
     *
     * @return bool True if the ban's expiration time has already passed
     */
    public function isExpired()
    {
        if ($this->expiration === null) {
            return false;
        }

        return TimeDate::now()->gte($this->expiration);
    }

    /**
     * Check whether the ban will expire automatically
     *
     * @return bool
     */
    public function willExpire()
    {
        return ($this->expiration !== null);
    }

    /**
     * Mark the ban as expired
     *
     * @return self
     */
    public function expire()
    {
        $this->setExpiration(TimeDate::now());
        $this->getVictim()->markAsUnbanned();

        return $this;
    }

    /**
     * Set the expiration date of the ban
     * @param  TimeDate $expiration The expiration
     * @return self
     */
    public function setExpiration($expiration)
    {
        if ($expiration !== null) {
            $expiration = TimeDate::from($expiration);
        }

        return $this->updateProperty($this->expiration, 'expiration', $expiration, 's');
    }

    /**
     * Set the server message of the ban
     * @param  string $message The new server message
     * @return self
     */
    public function setServerMessage($message)
    {
        return $this->updateProperty($this->srvmsg, 'server_message', $message, 's');
    }

    /**
     * Set the reason of the ban
     * @param  string $reason The new ban reason
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
     * @param  boolean $allowServerJoin
     * @return self
     */
    public function setAllowServerJoin($allowServerJoin)
    {
        return $this->updateProperty($this->allowServerJoin, 'allow_server_join', (bool) $allowServerJoin);
    }

    /**
     * Add a new ban
     *
     * @param int                              $playerID        The ID of the victim of the ban
     * @param int                              $authorID        The ID of the player responsible for the ban
     * @param BZIon\Form\Creator\TimeDate|null $expiration      The expiration of the ban (set to NULL so that it never expires)
     * @param string                           $reason          The full reason for the ban
     * @param string                           $srvmsg          A summary of the ban to be displayed on server banlists (max 150 characters)
     * @param string[]                         $ipAddresses     An array of IPs that have been banned
     * @param bool                             $allowServerJoin Whether or not the player is allowed to join match servers
     *
     * @return Ban An object representing the ban that was just entered or false if the ban was not created
     */
    public static function addBan($playerID, $authorID, $expiration, $reason, $srvmsg = "", $ipAddresses = array(), $allowServerJoin = false)
    {
        $player = Player::get($playerID);

        if ($expiration !== null) {
            $expiration = TimeDate::from($expiration)->toMysql();
        } else {
            $player->markAsBanned();
        }

        // If there are no IPs to banned or no server ban message, then we'll allow the players to join as observers
        if (empty($srvmsg) || empty($ipAddresses)) {
            $allowServerJoin = true;
        }

        $ban = self::create(array(
            'player'            => $playerID,
            'expiration'        => $expiration,
            'server_message'    => $srvmsg,
            'reason'            => $reason,
            'allow_server_join' => $allowServerJoin,
            'author'            => $authorID,
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

    /**
     * Get a query builder for news
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new QueryBuilder('Ban', array(
            'columns' => array(
                'status'  => 'status',
                'updated' => 'updated'
            ),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Ban against ' . $this->getVictim()->getUsername();
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        $this->getVictim()->markAsUnbanned();
        parent::delete();
    }

    /**
     * {@inheritDoc}
     */
    public static function getActiveStatuses()
    {
        return array('public');
    }

    /**
     * {@inheritDoc}
     */
    public static function getLazyColumns()
    {
        return null;
    }

    /**
     * Get all the bans in the database that aren't disabled or deleted
     * @return Ban[] An array of ban objects
     */
    public static function getBans()
    {
        return self::arrayIdToModel(self::fetchIds("ORDER BY updated DESC"));
    }

    /**
     * Get an active ban for the player
     * @param  int      $playerId The player's ID
     * @return Ban|null null if the player isn't currently banned
     */
    public static function getBan($playerId)
    {
        $bans = self::fetchIdsFrom('player', array($playerId), 'i', false, "AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())");

        if (empty($bans)) {
            return null;
        }

        return new Ban($bans[0]);
    }
}
