<?php
/**
 * This file contains functionality relating to a league player
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Util\StringUtils;

/**
 * A league player
 * @package    BZiON\Models
 */
class Player extends AvatarModel implements NamedModel
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
     * The player's status
     * @var string
     */
    protected $status;

    /**
     * The player's e-mail address
     * @var string
     */
    protected $email;

    /**
     * Whether the player has verified their e-mail address
     * @var bool
     */
    protected $verified;

    /**
     * What kind of events the player should be e-mailed about
     * @var string
     */
    protected $receives;

    /**
     * A confirmation code for the player's e-mail address verification
     * @var string
     */
    protected $confirmCode;

    /**
     * Whether the callsign of the player is outdated
     * @var boolean
     */
    protected $outdated;

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
     * The player's timezone PHP identifier, e.g. "Europe/Paris"
     * @var string
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
     * The location where avatars will be stored
     */
    const AVATAR_LOCATION = "/web/assets/imgs/avatars/players/";

    const EDIT_PERMISSION = Permission::EDIT_USER;
    const SOFT_DELETE_PERMISSION = Permission::SOFT_DELETE_USER;
    const HARD_DELETE_PERMISSION = Permission::HARD_DELETE_USER;

    /**
     * {@inheritDoc}
     */
    protected function assignResult($player)
    {
        $this->bzid = $player['bzid'];
        $this->name = $player['username'];
        $this->alias = $player['alias'];
        $this->team = $player['team'];
        $this->status = $player['status'];
        $this->avatar = $player['avatar'];
        $this->country = $player['country'];
    }

    /**
     * {@inheritDoc}
     */
    protected function assignLazyResult($player)
    {
        $this->email = $player['email'];
        $this->verified = $player['verified'];
        $this->receives = $player['receives'];
        $this->confirmCode = $player['confirm_code'];
        $this->outdated = $player['outdated'];
        $this->description = $player['description'];
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
        $this->lazyLoad();

        // Make sure the player doesn't already have the role
        foreach ($this->roles as $playerRole) {
            if ($playerRole->getId() == $role_id) {
                return false;
            }
        }

        return $this->modifyRole($role_id, "add");
    }

    /**
     * Get the notes admins have left about a player
     * @return string The notes
     */
    public function getAdminNotes()
    {
        $this->lazyLoad();

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
     * Get the e-mail address of the player
     *
     * @return string The address
     */
    public function getEmailAddress()
    {
        $this->lazyLoad();

        return $this->email;
    }

    /**
     * Returns whether the player has verified their e-mail address
     *
     * @return bool `true` for verified players
     */
    public function isVerified()
    {
        $this->lazyLoad();

        return $this->verified;
    }

    /**
     * Returns the confirmation code for the player's e-mail address verification
     *
     * @return string The player's confirmation code
     */
    public function getConfirmCode()
    {
        $this->lazyLoad();

        return $this->confirmCode;
    }

    /**
     * Returns what kind of events the player should be e-mailed about
     *
     * @return string The type of notifications
     */
    public function getReceives()
    {
        $this->lazyLoad();

        return $this->receives;
    }

    /**
     * Finds out whether the specified player wants and can receive an e-mail
     * message
     *
     * @param  string  $type
     * @return boolean `true` if the player should be sent an e-mail
     */
    public function canReceive($type)
    {
        $this->lazyLoad();

        if (!$this->email || !$this->isVerified()) {
            // Unverified e-mail means the user will receive nothing
            return false;
        }

        if ($this->receives == 'everything') {
            return true;
        }

        return ($this->receives == $type);
    }

    /**
     * Find out whether the specified confirmation code is correct
     *
     * This method protects against timing attacks
     *
     * @return bool `true` for a correct e-mail verification code
     */
    public function isCorrectConfirmCode($code)
    {
        $this->lazyLoad();

        if ($this->confirmCode === null) {
            return false;
        }

        return StringUtils::equals($code, $this->confirmCode);
    }

    /**
     * Get the player's sanitized description
     * @return string The description
     */
    public function getDescription()
    {
        $this->lazyLoad();

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
        $this->lazyLoad();

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
        $this->lazyLoad();

        if ($human) {
            return $this->last_login->diffForHumans();
        } else {
            return $this->last_login;
        }
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
        $this->lazyLoad();

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
     * Get the player's timezone PHP identifier (example: "Europe/Paris")
     * @return string The timezone
     */
    public function getTimezone()
    {
        $this->lazyLoad();

        return ($this->timezone) ?: date_default_timezone_get();
    }

    /**
     * Check if a player has a specific permission
     *
     * @param string|null $permission The permission to check for
     *
     * @return bool Whether or not the player has the permission
     */
    public function hasPermission($permission)
    {
        if ($permission === null) {
            return false;
        }

        $this->lazyLoad();

        return isset($this->permissions[$permission]);
    }

    /**
     * Check whether the callsign of the player is outdated
     *
     * Returns true if this player has probably changed their callsign, making
     * the current username stored in the database obsolete
     *
     * @return bool Whether or not the player is disabled
     */
    public function isOutdated()
    {
        $this->lazyLoad();

        return $this->outdated;
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
        $this->lazyLoad();
        return $this->modifyRole($role_id, "remove");
    }

    /**
     * Set the player's email address and reset their verification status
     * @param string $email The address
     */
    public function setEmailAddress($email)
    {
        $this->lazyLoad();

        if ($this->email == $email) {
            // The e-mail hasn't changed, don't do anything
            return;
        }

        $this->setVerified(false);
        $this->generateNewConfirmCode();

        $this->email = $email;
        $this->update("email", $email, 's');
    }

    /**
     * Set whether the player has verified their e-mail address
     *
     * @param  bool $verified Whether the player is verified or not
     * @return self
     */
    public function setVerified($verified)
    {
        $this->lazyLoad();

        if ($verified) {
            $this->setConfirmCode(null);
        }

        return $this->updateProperty($this->verified, 'verified', $verified, 'i');
    }

    /**
     * Generate a new random confirmation token for e-mail address verification
     *
     * @return self
     */
    public function generateNewConfirmCode()
    {
        $generator = new SecureRandom();
        $random = $generator->nextBytes(16);

        return $this->setConfirmCode(bin2hex($random));
    }

    /**
     * Set the confirmation token for e-mail address verification
     *
     * @param  string $code The confirmation code
     * @return self
     */
    private function setConfirmCode($code)
    {
        return $this->updateProperty($this->confirmCode, 'confirm_code', $code, 's');
    }

    /**
     * Set what kind of events the player should be e-mailed about
     *
     * @param  string $receives The type of notification
     * @return self
     */
    public function setReceives($receives)
    {
        return $this->updateProperty($this->receives, 'receives', $receives, 's');
    }

    /**
     * Set whether the callsign of the player is outdated
     *
     * @param  boolean $outdated Whether the callsign is outdated
     * @return self
     */
    public function setOutdated($outdated)
    {
        return $this->updateProperty($this->outdated, 'outdated', $outdated, 'i');
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
     * Set the player's country
     * @param  string|int $country The ID or ISO code of the new country
     * @return self
     */
    public function setCountry($country)
    {
        if (is_string($country)) {
            // $country is an ISO code, convert it to a database ID
            $country = Country::getIdFromISO($country);
        }

        return $this->updateProperty($this->country, 'country', $country, 'i');
    }

    /**
     * Updates this player's last login
     */
    public function updateLastLogin()
    {
        $this->update("last_login", TimeDate::now()->toMysql(), 's');
    }

    /**
     * Get the player's username
     * @return string The username
     */
    public function getUsername()
    {
        return $this->name;
    }

    /**
     * Get the player's username, safe for use in your HTML
     * @return string The username
     */
    public function getEscapedUsername()
    {
        return $this->getEscapedName();
    }

    /**
     * Alias for Player::setUsername()
     *
     * @param  string $username The new username
     * @return self
     */
    public function setName($username)
    {
        return $this->setUsername($username);
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
            parent::fetchIdsFrom("status", array("active", "test"), "s", false)
        );
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
        return $this->fetchCount("WHERE `player` = ? AND `read` = 0",
            'i', $this->id, 'player_groups'
        );
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
     * {@inheritDoc}
     */
    public static function getActiveStatuses()
    {
        return array('active', 'reported', 'test');
    }

    /**
     * {@inheritDoc}
     */
    public static function getEagerColumns()
    {
        return 'id,bzid,team,username,alias,status,avatar,country';
    }

    /**
     * {@inheritDoc}
     */
    public static function getLazyColumns()
    {
        return 'email,verified,receives,confirm_code,outdated,description,timezone,joined,last_login,admin_notes';
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
                'team'     => 'team',
                'outdated' => 'outdated',
                'status'   => 'status'
            ),
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
     * @param  string           $timezone    The player's timezone
     * @param  string|\TimeDate $joined      The date the player joined
     * @param  string|\TimeDate $last_login  The timestamp of the player's last login
     * @return Player           An object representing the player that was just entered
     */
    public static function newPlayer($bzid, $username, $team = null, $status = "active", $role_id = self::PLAYER, $avatar = "", $description = "", $country = 1, $timezone = null, $joined = "now", $last_login = "now")
    {
        $joined = TimeDate::from($joined);
        $last_login = TimeDate::from($last_login);
        $timezone = ($timezone) ?: date_default_timezone_get();

        $player = self::create(array(
            'bzid'        => $bzid,
            'team'        => $team,
            'username'    => $username,
            'alias'       => self::generateAlias($username),
            'status'      => $status,
            'avatar'      => $avatar,
            'description' => $description,
            'country'     => $country,
            'timezone'    => $timezone,
            'joined'      => $joined->toMysql(),
            'last_login'  => $last_login->toMysql(),
        ), 'iisssssisss');

        $player->addRole($role_id);
        $player->getIdenticon($player->getId());

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
     * Change a player's callsign and add it to the database if it does not
     * exist as a past callsign
     *
     * @param  string $username The new username of the player
     * @return self
     */
    public function setUsername($username)
    {
        // The player's username was just fetched from BzDB, it's definitely not
        // outdated
        $this->setOutdated(false);

        // Players who have this player's username are considered outdated
        $this->db->query("UPDATE {$this->table} SET outdated = 1 WHERE username = ? AND id != ?", "si", array($username, $this->id));

        if ($username === $this->name) {
            // The player's username hasn't changed, no need to do anything
            return $this;
        }

        // Players who used to have our player's username are not outdated anymore,
        // unless they are more than one.
        // Even though we are sure that the old and new usernames are not equal,
        // MySQL makes a different type of string equality tests, which is why we
        // also check IDs to make sure not to affect our own player's outdatedness.
        $this->db->query("
            UPDATE {$this->table} SET outdated =
                (SELECT (COUNT(*)>1) FROM (SELECT 1 FROM {$this->table} WHERE username = ? AND id != ?) t)
            WHERE username = ? AND id != ?",
            "sisi", array($this->name, $this->id, $this->name, $this->id));

        $this->updateProperty($this->name, 'username', $username, 's');
        $this->db->query("INSERT IGNORE INTO past_callsigns (player, username) VALUES (?, ?)", "is", array($this->id, $username));
        $this->resetAlias();

        return $this;
    }

    /**
     * Alphabetical order function for use in usort (case-insensitive)
     * @return Closure The sort function
     */
    public static function getAlphabeticalSort()
    {
        return function (Player $a, Player $b) {
            return strcasecmp($a->getUsername(), $b->getUsername());
        };
    }

    /**
     * Find whether the player can delete a model
     *
     * @param  PermissionModel $model       The model that will be seen
     * @param  boolean         $showDeleted Whether to show deleted models to admins
     * @return boolean
     */
    public function canSee($model, $showDeleted = false)
    {
        return $model->canBeSeenBy($this, $showDeleted);
    }

    /**
     * Find whether the player can delete a model
     *
     * @param  PermissionModel $model The model that will be deleted
     * @param  boolean         $hard  Whether to check for hard-delete perms, as opposed
     *                                to soft-delete ones
     * @return boolean
     */
    public function canDelete($model, $hard = false)
    {
        if ($hard) {
            return $model->canBeHardDeletedBy($this);
        } else {
            return $model->canBeSoftDeletedBy($this);
        }
    }

    /**
     * Find whether the player can create a model
     *
     * @param  string  $modelName The PHP class identifier of the model type
     * @return boolean
     */
    public function canCreate($modelName)
    {
        return $modelName::canBeCreatedBy($this);
    }

    /**
     * Find whether the player can edit a model
     *
     * @param  PermissionModel $model The model which will be edited
     * @return boolean
     */
    public function canEdit($model)
    {
        return $model->canBeEditedBy($this);
    }
}
