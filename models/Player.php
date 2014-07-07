<?php
/**
 * This file contains functionality relating to a league player
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A league player
 * @package    BZiON\Models
 */
class Player extends IdenticonModel implements NamedModel
{
    /**
     * These are built-in roles that cannot be deleted via the web interface so we will be storing these values as
     * constant variables. Hopefully, a user won't be silly enough to delete them manually from the database.
     */
    const DEVELOPER    = 1;
    const ADMIN        = 2;
    const COP          = 3;
    const REFEREE      = 4;
    const S_ADMIN      = 5;
    const PLAYER       = 6;
    const PLAYER_NO_PM = 7;

    /**
     * The bzid of the player
     * @var int
     */
    protected $bzid;

    /**
     * The id of the player's team
     * @var int
     */
    protected $team;

    /**
     * The username of the player
     * @var string
     */
    protected $username;

    /**
     * The player's status
     * @var string
     */
    protected $status;

    /**
     * The url of the player's profile avatar
     * @var string
     */
    protected $avatar;

    /**
     * The player's profile description
     * @var string
     */
    protected $description;

    /**
     * The id of the player's country
     * @var int
     */
    protected $country;

    /**
     * The player's timezone, in terms of distance from UTC (i.e. -5 for UTC-5)
     * @var int
     */
    protected $timezone;

    /**
     * The date the player joined the site
     * @var TimeDate
     */
    protected $joined;

    /**
     * The date of the player's last login
     * @var TimeDate
     */
    protected $last_login;

    /**
     * The roles a player belongs to
     * @var Role[]
     */
    protected $roles;

    /**
     * The permissions a player has
     * @var Permission[]
     */
    protected $permissions;

    /**
     * A section for admins to write notes about players
     * @var string
     */
    protected $admin_notes;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "players";

    /**
     * The location of identicons will stored in
     */
    const IDENTICON_LOCATION = "/assets/imgs/identicons/players/";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($player)
    {
        $this->bzid = $player['bzid'];
        $this->username = $player['username'];
        $this->alias = $player['alias'];
        $this->team = $player['team'];
        $this->status = $player['status'];
        $this->avatar = $player['avatar'];
        $this->description = $player['description'];
        $this->country = $player['country'];
        $this->timezone = $player['timezone'];
        $this->joined = new TimeDate($player['joined']);
        $this->last_login = new TimeDate($player['last_login']);
        $this->admin_notes = $player['admin_notes'];

        $this->roles = Role::getRoles($this->id);
        $this->permissions = array();

        foreach ($this->roles as $role) {
            $this->permissions = array_merge($this->permissions, $role->getPerms());
        }
    }

    /**
     * Add a player a new role
     *
     * @param int $role_id The role ID to add a player to
     *
     * @return bool Whether the operation was successful or not
     */
    public function addRole($role_id)
    {
        return $this->modifyRole($role_id, "add");
    }

    /**
     * Get the player's avatar
     * @return string The URL for the avatar
     */
    public function getAvatar()
    {
        if (!empty($this->avatar)) {
            return $this->avatar;
        }

        return $this->getIdenticon($this->getUsername(), $this->getBZID());
    }

    /**
     * Get the player's avatar as an HTML image element
     *
     * @return string The HTML for the image
     */
    public function getAvatarLiteral()
    {
        return '<img class="player_avatar" src="' . $this->getAvatar() . '">';
    }

    /**
     * Get the notes admins have left about a player
     * @return string The notes
     */
    public function getAdminNotes()
    {
        return $this->admin_notes;
    }

    /**
     * Get the player's BZID
     * @return int The BZID
     */
    public function getBZID()
    {
        return $this->bzid;
    }

    /**
     * Get the country a player belongs to
     *
     * @return Country The country belongs to
     */
    public function getCountry()
    {
        return new Country($this->country);
    }

    /**
     * Get the player's sanitized description
     * @return string The description
     */
    public function getDescription()
    {
        return htmlspecialchars($this->description);
    }

    /**
     * Get the joined date of the player
     *
     * @param string $format
     *
     * @return string The joined date of the player
     */
    public function getJoinedDate($format = "")
    {
        if (empty($format)) {
            return $this->joined->diffForHumans();
        }

        return $this->joined->format($format);
    }

    /**
     * Get all of the known IPs used by the player
     *
     * @return string[][] An array containing IPs and hosts
     */
    public function getKnownIPs()
    {
        return $this->db->query("SELECT DISTINCT ip, host FROM visits WHERE player = ?", "i", array($this->getId()));
    }

    /**
     * Get the last login for a player
     * @param  bool   $human Whether to get the literal time stamp or a relative time
     * @return string The date of the last login
     */
    public function getLastLogin($human = true)
    {
        if ($human)
            return $this->last_login->diffForHumans();
        else
            return $this->last_login;
    }

    /**
     * Get all of the callsigns a player has used to log in to the website
     * @return string[] An array containing all of the past callsigns recorded for a player
     */
    public function getPastCallsigns()
    {
        return parent::fetchIds("WHERE player = ?", "i", array($this->id), "past_callsigns", "username");
    }

    /**
     * Get the player's description, exactly as it is saved in the database
     * @return string The description
     */
    public function getRawDescription()
    {
        return $this->description;
    }

    /**
     * Get the player's team
     * @return Team The object representing the team
     */
    public function getTeam()
    {
        return new Team($this->team);
    }

    /**
     * Get the player's timezone
     * @return integer The timezone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Check if a player has a specific permission
     *
     * @param string $permission The permission to check for
     *
     * @return bool Whether or not the player has the permission
     */
    public function hasPermission($permission)
    {
        return isset($this->permissions[$permission]);
    }

    /**
     * Check if a player's account has been disabled
     *
     * @return bool Whether or not the player is disabled
     */
    public function isDisabled()
    {
        return ($this->status == "disabled");
    }

    /**
     * Check if everyone can log in as this user on a test environment
     *
     * @return bool
     */
    public function isTestUser()
    {
        return ($this->status == "test");
    }

    /**
     * Check if a player is teamless
     *
     * @return bool True if the player is teamless
     */
    public function isTeamless()
    {
        return (empty($this->team));
    }

    /**
     * Mark a player's account as banned
     */
    public function markAsBanned()
    {
        if ($this->status != 'active') {
            return $this;
        }

        return $this->updateProperty($this->status, "status", "banned", 's');
    }

    /**
     * Mark a player's account as unbanned
     */
    public function markAsUnbanned()
    {
        if ($this->status != 'banned') {
            return $this;
        }

        return $this->updateProperty($this->status, "status", "active", 's');
    }

    /**
     * Find out if a player is banned
     */
    public function isBanned()
    {
        return (Ban::getBan($this->id) !== null);
    }

    /**
     * Remove a player from a role
     *
     * @param int $role_id The role ID to add or remove
     *
     * @return bool Whether the operation was successful or not
     */
    public function removeRole($role_id)
    {
        return $this->modifyRole($role_id, "remove");
    }

    /**
     * Set the player's avatar
     * @param string $avatar The URL for the avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        $this->update("avatar", $avatar, 's');
    }

    /**
     * Set the player's description
     * @param string $description The description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        $this->update("description", $description, 's');
    }

    /**
     * Set the player's timezone
     * @param string $timezone The timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        $this->update("timezone", $timezone, 's');
    }

    /**
     * Set the player's team
     * @param int $team The team's ID
     */
    public function setTeam($team)
    {
        $this->team = $team;
        $this->update("team", $team, 'i');
    }

    /**
     * Set the player's admin notes
     * @param  string $admin_notes The new admin notes
     * @return self
     */
    public function setAdminNotes($admin_notes)
    {
	return $this->updateProperty($this->admin_notes, 'admin_notes', $admin_notes, 's');
    }

    /**
     * Updates this player's last login
     * @todo Make me work
     */
    public function updateLastLogin()
    {
        $this->update("last_login", "now", 's');
    }

    /**
     * Get the player's username
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getUsername();
    }

    /**
     * Get the player's, safe for use in your HTML
     * @return string The username
     */
    public function getEscapedUsername()
    {
        return $this->escape($this->username);
    }

    /**
     * Give or remove a role to/form a player
     *
     * @param int    $role_id The role ID to add or remove
     * @param string $action  Whether to "add" or "remove" a role for a player
     *
     * @return bool Whether the operation was successful or not
     */
    private function modifyRole($role_id, $action)
    {
        $role = new Role($role_id);

        if ($role->isValid()) {
            if ($action == "add") {
                $this->db->query("INSERT INTO player_roles (user_id, role_id) VALUES (?, ?)", "ii", array($this->getId(), $role_id));
            } elseif ($action == "remove") {
                $this->db->query("DELETE FROM player_roles WHERE user_id = ? AND role_id = ?", "ii", array($this->getId(), $role_id));
            }
            $this->refresh();

            return true;
        }

        return false;
    }

    /**
     * Given a player's BZID, get a player object
     *
     * @param  int    $bzid The player's BZID
     * @return Player
     */
    public static function getFromBZID($bzid)
    {
        return new Player(self::fetchIdFrom($bzid, "bzid", "s"));
    }

    /**
     * Get a single player by their username
     *
     * @param  string $username The username to look for
     * @return Player
     */
    public static function getFromUsername($username)
    {
        return new Player(self::fetchIdFrom($username, 'username', 's'));
    }

    /**
     * Get all the players in the database that have an active status
     * @return Player[] An array of player BZIDs
     */
    public static function getPlayers()
    {
        return self::arrayIdToModel(
            parent::fetchIdsFrom("status", array("active"), "s", false)
        );
    }

    /**
     * Send a notification to a player
     * @param  string       $message The content of the notification
     * @return Notification The sent notification
     */
    public function notify($message)
    {
        return Notification::newNotification($this->getId(), $message);
    }

    /**
     * Show the number of notifications the user hasn't read yet
     * @return int
     */
    public function countUnreadNotifications()
    {
        return Notification::countUnreadNotifications($this->id);
    }

    /**
     * Show the number of messages the user hasn't read yet
     * @return int
     */
    public function countUnreadMessages()
    {
        $result = $this->db->query(
            "SELECT COUNT(*) FROM `player_groups` WHERE `player` = ? AND `read` = 0",
            'i', $this->id);

        return $result[0]['COUNT(*)'];
    }

    /**
     * Get all of the members belonging to a team
     * @param  int      $teamID The ID of the team to fetch the members of
     * @return Player[] An array of Player objects of the team members
     */
    public static function getTeamMembers($teamID)
    {
        return self::arrayIdToModel(
            parent::fetchIds("WHERE team = ?", "i", array($teamID))
        );
    }

    /**
     * Get a query builder for players
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new QueryBuilder('Player', array(
            'columns' => array(
                'username' => 'username',
                'team' => 'team'
            ),
            'activeStatuses' => array('active', 'test'),
            'name' => 'username',
        ));
    }

    /**
     * Enter a new player to the database
     * @param  int              $bzid        The player's bzid
     * @param  string           $username    The player's username
     * @param  int              $team        The player's team
     * @param  string           $status      The player's status
     * @param  int              $role_id     The player's role when they are first created
     * @param  string           $avatar      The player's profile avatar
     * @param  string           $description The player's profile description
     * @param  int              $country     The player's country
     * @param  int              $timezone    The player's timezone
     * @param  string|\TimeDate $joined      The date the player joined
     * @param  string|\TimeDate $last_login  The timestamp of the player's last login
     * @return Player           An object representing the player that was just entered
     */
    public static function newPlayer($bzid, $username, $team=null, $status="active", $role_id=self::PLAYER, $avatar="", $description="", $country=1, $timezone=0, $joined="now", $last_login="now")
    {
        $joined = TimeDate::from($joined);
        $last_login = TimeDate::from($last_login);

        $player = self::create(array(
            'bzid' => $bzid,
            'team' => $team,
            'username' => $username,
            'alias' => self::generateAlias($username),
            'status' => $status,
            'avatar' => $avatar,
            'description' => $description,
            'country' => $country,
            'timezone' => $timezone,
            'joined' => $joined->toMysql(),
            'last_login' => $last_login->toMysql(),
        ), 'iisssssiiss');

        $player->addRole($role_id);

        return $player;
    }

    /**
     * Determine if a player exists in the database
     * @param  int  $bzid The player's bzid
     * @return bool Whether the player exists in the database
     */
    public static function playerBZIDExists($bzid)
    {
        return self::getFromBZID($bzid)->isValid();
    }

    /**
     * Add a player's callsign to the database if it does not exist as a past callsign
     * @param string $id       The ID for the player whose callsign we're saving
     * @param string $username The callsign which we are saving if it doesn't exist
     */
    public static function saveUsername($id, $username)
    {
        $db = Database::getInstance();

        $db->query("INSERT IGNORE INTO past_callsigns (id, player, username) VALUES (?, ?, ?)", "iis", array(NULL, $id, $username));
    }

    /**
     * Alphabetical order function for use in usort (case-insensitive)
     * @return callable The sort function
     */
    public static function getAlphabeticalSort()
    {
        return function (Player $a, Player $b) {
            return strcasecmp($a->getUsername(), $b->getUsername());
        };
    }
}
