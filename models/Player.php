<?php
/**
 * This file contains functionality relating to a league player
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use Carbon\Carbon;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Util\StringUtils;

/**
 * A league player
 * @package    BZiON\Models
 */
class Player extends AvatarModel implements NamedModel, DuplexUrlInterface, EloInterface
{
    /**
     * These are built-in roles that cannot be deleted via the web interface so we will be storing these values as
     * constant variables. Hopefully, a user won't be silly enough to delete them manually from the database.
     *
     * @TODO Deprecate these and use the Role constants
     */
    const DEVELOPER    = Role::DEVELOPER;
    const ADMIN        = Role::ADMINISTRATOR;
    const COP          = Role::COP;
    const REFEREE      = Role::REFEREE;
    const S_ADMIN      = Role::SYSADMIN;
    const PLAYER       = Role::PLAYER;
    const PLAYER_NO_PM = Role::PLAYER_NO_PM;

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
     * @var bool
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
     * The site theme this player has chosen
     * @var string
     */
    protected $theme;

    /**
     * Whether or not this player has opted-in for color blindness assistance.
     * @var bool
     */
    protected $color_blind_enabled;

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
     * The date of the player's last match
     * @var Match
     */
    protected $last_match;

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
     * The ban of the player, or null if the player is not banned
     * @var Ban|null
     */
    protected $ban;

    /**
     * Cached results for match summaries
     *
     * @var array
     */
    private $cachedMatchSummary;

    /**
     * The cached match count for a player
     *
     * @var int
     */
    private $cachedMatchCount = null;

    /**
     * The Elo for this player that has been explicitly set for this player from a database query. This value will take
     * precedence over having to build to an Elo season history.
     *
     * @var int
     */
    private $elo;
    private $eloSeason;
    private $eloSeasonHistory;

    private $matchActivity;

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
     * {@inheritdoc}
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

        if (array_key_exists('activity', $player)) {
            $this->matchActivity = ($player['activity'] != null) ? $player['activity'] : 0.0;
        }

        if (array_key_exists('elo', $player)) {
            $this->elo = $player['elo'];
        }
    }

    /**
     * {@inheritdoc}
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
        $this->joined = TimeDate::fromMysql($player['joined']);
        $this->last_login = TimeDate::fromMysql($player['last_login']);
        $this->last_match = Match::get($player['last_match']);
        $this->admin_notes = $player['admin_notes'];
        $this->ban = Ban::getBan($this->id);
        $this->color_blind_enabled = $player['color_blind_enabled'];

        $this->cachedMatchSummary = [];

        // Theme user options
        if (isset($player['theme'])) {
            $this->theme = $player['theme'];
        } else {
            $themes = Service::getSiteThemes();
            $this->theme = $themes[0]['slug'];
        }

        $this->updateUserPermissions();
    }

    /**
     * Add a player a new role
     *
     * @param Role|int $role_id The role ID to add a player to
     *
     * @return bool Whether the operation was successful or not
     */
    public function addRole($role_id)
    {
        if ($role_id instanceof Role) {
            $role_id = $role_id->getId();
        }

        $this->lazyLoad();

        // Make sure the player doesn't already have the role
        foreach ($this->roles as $playerRole) {
            if ($playerRole->getId() == $role_id) {
                return false;
            }
        }

        $status = $this->modifyRole($role_id, "add");
        $this->refresh();

        return $status;
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
        return Country::get($this->country);
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
     * Build a key that we'll use for caching season Elo data in this model
     *
     * @param  string|null $season The season to get
     * @param  int|null    $year   The year of the season to get
     *
     * @return string
     */
    private function buildSeasonKey(&$season, &$year)
    {
        if ($season === null) {
            $season = Season::getCurrentSeason();
        }

        if ($year === null) {
            $year = Carbon::now()->year;
        }

        return sprintf('%s-%s', $year, $season);
    }

    /**
     * Build a key to use for caching season Elo data in this model from a timestamp
     *
     * @param DateTime $timestamp
     *
     * @return string
     */
    private function buildSeasonKeyFromTimestamp(\DateTime $timestamp)
    {
        $seasonInfo = Season::getSeason($timestamp);

        return sprintf('%s-%s', $seasonInfo['year'], $seasonInfo['season']);
    }

    /**
     * Remove all Elo data for this model for matches occurring after the given match (inclusive)
     *
     * This function will not remove the Elo data for this match from the database. Ideally, this function should only
     * be called during Elo recalculation for this match.
     *
     * @internal
     *
     * @param Match $match
     *
     * @see Match::recalculateElo()
     */
    public function invalidateMatchFromCache(Match $match)
    {
        $seasonKey = $this->buildSeasonKeyFromTimestamp($match->getTimestamp());
        $seasonElo = null;

        $this->getEloSeasonHistory();

        if (!isset($this->eloSeasonHistory[$seasonKey][$match->getId()])) {
            return;
        }

        $eloChangelogIndex = array_search($match->getId(), array_keys($this->eloSeasonHistory[$seasonKey]));
        $slicedChangeLog = array_slice($this->eloSeasonHistory[$seasonKey], 0, $eloChangelogIndex, true);

        $this->eloSeasonHistory[$seasonKey] = $slicedChangeLog;
        $this->eloSeason[$seasonKey] = end($slicedChangeLog)['elo'];
    }

    /**
     * Get the Elo changes for a player for a given season
     *
     * @param  string|null $season The season to get
     * @param  int|null    $year   The year of the season to get
     *
     * @return array
     */
    public function getEloSeasonHistory($season = null, $year = null)
    {
        $seasonKey = $this->buildSeasonKey($season, $year);

        // This season's already been cached
        if (isset($this->eloSeasonHistory[$seasonKey])) {
            return $this->eloSeasonHistory[$seasonKey];
        }

        $result = $this->db->query('
          SELECT
            elo_new AS elo,
            match_id AS `match`,
            MONTH(matches.timestamp) AS `month`,
            YEAR(matches.timestamp) AS `year`,
            DAY(matches.timestamp) AS `day`
          FROM
            player_elo
            LEFT JOIN matches ON player_elo.match_id = matches.id
          WHERE
            user_id = ? AND season_period = ? AND season_year = ?
          ORDER BY
            match_id ASC
        ', [ $this->getId(), $season, $year ]);

        $this->eloSeasonHistory[$seasonKey] = [[
            'elo' => 1200,
            'match' => null,
            'month' => Season::getCurrentSeasonRange($season)->getStartOfRange()->month,
            'year' => $year,
            'day' => 1
        ]] + array_combine(array_column($result, 'match'), $result);

        return $this->eloSeasonHistory[$seasonKey];
    }

    /**
     * Get the player's Elo for a season.
     *
     * With the default arguments, it will fetch the Elo for the current season.
     *
     * @param string|null $season The season we're looking for: winter, spring, summer, or fall
     * @param int|null    $year   The year of the season we're looking for
     *
     * @return int The player's Elo
     */
    public function getElo($season = null, $year = null)
    {
        // The Elo for this player has been forcefully set from a trusted database query, so just return that.
        if ($this->elo !== null) {
            return $this->elo;
        }

        $this->getEloSeasonHistory($season, $year);
        $seasonKey = $this->buildSeasonKey($season, $year);

        if (isset($this->eloSeason[$seasonKey])) {
            return $this->eloSeason[$seasonKey];
        }

        $season = &$this->eloSeasonHistory[$seasonKey];

        if (!empty($season)) {
            $elo = end($season);
            $this->eloSeason[$seasonKey] = ($elo !== false) ? $elo['elo'] : 1200;
        } else {
            $this->eloSeason[$seasonKey] = 1200;
        }

        return $this->eloSeason[$seasonKey];
    }

    /**
     * Adjust the Elo of a player for the current season based on a Match
     *
     * **Warning:** If $match is null, the Elo for the player will be modified but the value will not be persisted to
     * the database.
     *
     * @param int        $adjust The value to be added to the current ELO (negative to subtract)
     * @param Match|null $match  The match where this Elo change took place
     */
    public function adjustElo($adjust, Match $match = null)
    {
        $timestamp = ($match !== null) ? $match->getTimestamp() : (Carbon::now());
        $seasonInfo = Season::getSeason($timestamp);

        // Get the current Elo for the player, even if it's the default 1200. We need the value for adjusting
        $elo = $this->getElo($seasonInfo['season'], $seasonInfo['year']);
        $seasonKey = sprintf('%s-%s', $seasonInfo['year'], $seasonInfo['season']);

        $this->eloSeason[$seasonKey] += $adjust;

        if ($match !== null && $this->isValid()) {
            $this->eloSeasonHistory[$seasonKey][$match->getId()] = [
                'elo' => $this->eloSeason[$seasonKey],
                'match' => $match->getId(),
                'month' => $match->getTimestamp()->month,
                'year' => $match->getTimestamp()->year,
                'day' => null,
            ];

            $this->db->execute('
              INSERT INTO player_elo VALUES (?, ?, ?, ?, ?, ?)
            ', [ $this->getId(), $match->getId(), $seasonInfo['season'], $seasonInfo['year'], $elo, $this->eloSeason[$seasonKey] ]);
        }
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
     * @return bool `true` if the player should be sent an e-mail
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

        return $this->receives == $type;
    }

    /**
     * Find out whether the specified confirmation code is correct
     *
     * This method protects against timing attacks
     *
     * @param  string $code The confirmation code to check
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

        return $this->description;
    }

    /**
     * Get the joined date of the player
     * @return TimeDate The joined date of the player
     */
    public function getJoinedDate()
    {
        $this->lazyLoad();

        return $this->joined->copy();
    }

    /**
     * Get all of the known IPs used by the player
     *
     * @return string[][] An array containing IPs and hosts
     */
    public function getKnownIPs()
    {
        return $this->db->query(
            'SELECT DISTINCT ip, host FROM visits WHERE player = ? GROUP BY ip, host ORDER BY MAX(timestamp) DESC LIMIT 10',
            array($this->getId())
        );
    }

    /**
     * Get the last login for a player
     * @return TimeDate The date of the last login
     */
    public function getLastLogin()
    {
        $this->lazyLoad();

        return $this->last_login->copy();
    }

    /**
     * Get the last match
     * @return Match
     */
    public function getLastMatch()
    {
        $this->lazyLoad();

        return $this->last_match;
    }

    /**
     * Get all of the callsigns a player has used to log in to the website
     * @return string[] An array containing all of the past callsigns recorded for a player
     */
    public function getPastCallsigns()
    {
        return self::fetchIds("WHERE player = ?", array($this->id), "past_callsigns", "username");
    }

    /**
     * Get the player's team
     * @return Team The object representing the team
     */
    public function getTeam()
    {
        return Team::get($this->team);
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
     * Get the roles of the player
     * @return Role[]
     */
    public function getRoles()
    {
        $this->lazyLoad();

        return $this->roles;
    }

    /**
     * Rebuild the list of permissions a user has been granted
     */
    private function updateUserPermissions()
    {
        $this->roles = Role::getRoles($this->id);
        $this->permissions = array();

        foreach ($this->roles as $role) {
            $this->permissions = array_merge($this->permissions, $role->getPerms());
        }
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
     * Check whether or not a player been in a match or has logged on in the specified amount of time to be considered
     * active
     *
     * @return bool True if the player has been active
     */
    public function hasBeenActive()
    {
        $this->lazyLoad();

        $interval  = Service::getParameter('bzion.miscellaneous.active_interval');
        $lastLogin = $this->last_login->copy()->modify($interval);

        $hasBeenActive = (TimeDate::now() <= $lastLogin);

        if ($this->last_match->isValid()) {
            $lastMatch = $this->last_match->getTimestamp()->copy()->modify($interval);
            $hasBeenActive = ($hasBeenActive || TimeDate::now() <= $lastMatch);
        }

        return $hasBeenActive;
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
        return $this->status == "disabled";
    }

    /**
     * Check if everyone can log in as this user on a test environment
     *
     * @return bool
     */
    public function isTestUser()
    {
        return $this->status == "test";
    }

    /**
     * Check if a player is teamless
     *
     * @return bool True if the player is teamless
     */
    public function isTeamless()
    {
        return empty($this->team);
    }

    /**
     * Mark a player's account as banned
     *
     * @deprecated The players table shouldn't have any indicators of banned status, the Bans table is the authoritative source
     */
    public function markAsBanned()
    {
        return;
    }

    /**
     * Mark a player's account as unbanned
     *
     * @deprecated The players table shouldn't have any indicators of banned status, the Bans table is the authoritative source
     */
    public function markAsUnbanned()
    {
        return;
    }

    /**
     * Find out if a player is hard banned
     *
     * @return bool
     */
    public function isBanned()
    {
        $ban = Ban::getBan($this->id);

        return ($ban !== null && !$ban->isSoftBan());
    }

    /**
     * Get the ban of the player
     *
     * This method performs a load of all the lazy parameters of the Player
     *
     * @return Ban|null The current ban of the player, or null if the player is
     *                  is not banned
     */
    public function getBan()
    {
        $this->lazyLoad();

        return $this->ban;
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
        $status = $this->modifyRole($role_id, "remove");
        $this->refresh();

        return $status;
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

        $this->updateProperty($this->email, 'email', $email);
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

        return $this->updateProperty($this->verified, 'verified', $verified);
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
        $this->lazyLoad();

        return $this->updateProperty($this->confirmCode, 'confirm_code', $code);
    }

    /**
     * Set what kind of events the player should be e-mailed about
     *
     * @param  string $receives The type of notification
     * @return self
     */
    public function setReceives($receives)
    {
        $this->lazyLoad();

        return $this->updateProperty($this->receives, 'receives', $receives);
    }

    /**
     * Set whether the callsign of the player is outdated
     *
     * @param  bool $outdated Whether the callsign is outdated
     * @return self
     */
    public function setOutdated($outdated)
    {
        $this->lazyLoad();

        return $this->updateProperty($this->outdated, 'outdated', $outdated);
    }

    /**
     * Set the player's description
     * @param string $description The description
     */
    public function setDescription($description)
    {
        $this->updateProperty($this->description, "description", $description);
    }

    /**
     * Set the player's timezone
     * @param string $timezone The timezone
     */
    public function setTimezone($timezone)
    {
        $this->updateProperty($this->timezone, "timezone", $timezone);
    }

    /**
     * Set the player's team
     * @param int $team The team's ID
     */
    public function setTeam($team)
    {
        $this->updateProperty($this->team, "team", $team);
    }

    /**
     * Set the match the player last participated in
     *
     * @param int $match The match's ID
     */
    public function setLastMatch($match)
    {
        $this->updateProperty($this->last_match, 'last_match', $match);
    }

    /**
     * Set the player's status
     * @param string $status The new status
     */
    public function setStatus($status)
    {
        $this->updateProperty($this->status, 'status', $status);
    }

    /**
     * Set the player's admin notes
     * @param  string $admin_notes The new admin notes
     * @return self
     */
    public function setAdminNotes($admin_notes)
    {
        return $this->updateProperty($this->admin_notes, 'admin_notes', $admin_notes);
    }

    /**
     * Set the player's country
     * @param  int   $country The ID of the new country
     * @return self
     */
    public function setCountry($country)
    {
        return $this->updateProperty($this->country, 'country', $country);
    }

    /**
     * Get the player's chosen theme preference
     *
     * @return string
     */
    public function getTheme()
    {
        $this->lazyLoad();

        return $this->theme;
    }

    /**
     * Set the site theme for the player
     *
     * If the chosen site theme is invalid, it'll be defaulted to the site default (the first theme defined)
     *
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $themes = array_column(Service::getSiteThemes(), 'slug');

        if (!in_array($theme, $themes)) {
            $theme = Service::getDefaultSiteTheme();
        }

        return $this->updateProperty($this->theme, 'theme', $theme);
    }

    /**
     * Whether or not this player has color blind assistance enabled.
     *
     * @return bool
     */
    public function hasColorBlindAssist()
    {
        $this->lazyLoad();

        return (bool)$this->color_blind_enabled;
    }

    /**
     * Set a player's setting for color blind assistance.
     *
     * @param bool $enabled
     *
     * @return self
     */
    public function setColorBlindAssist($enabled)
    {
        return $this->updateProperty($this->color_blind_enabled, 'color_blind_enabled', $enabled);
    }

    /**
     * Updates this player's last login
     */
    public function updateLastLogin()
    {
        $this->update("last_login", TimeDate::now()->toMysql());
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
     * Mark all the unread messages of a player as read
     *
     * @return void
     */
    public function markMessagesAsRead()
    {
        $this->db->execute(
            "UPDATE `player_conversations` SET `read` = 1 WHERE `player` = ? AND `read` = 0",
            array($this->id)
        );
    }

    /**
     * Set the roles of a user
     *
     * @todo   Is it worth making this faster?
     * @param  Role[] $roles The new roles of the user
     * @return self
     */
    public function setRoles($roles)
    {
        $this->lazyLoad();

        $oldRoles = Role::mapToIds($this->roles);
        $this->roles = $roles;
        $roleIds = Role::mapToIds($roles);

        $newRoles     = array_diff($roleIds, $oldRoles);
        $removedRoles = array_diff($oldRoles, $roleIds);

        foreach ($newRoles as $role) {
            $this->modifyRole($role, 'add');
        }

        foreach ($removedRoles as $role) {
            $this->modifyRole($role, 'remove');
        }

        $this->refresh();

        return $this;
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
        $role = Role::get($role_id);

        if ($role->isValid()) {
            if ($action == "add") {
                $this->db->execute("INSERT INTO player_roles (user_id, role_id) VALUES (?, ?)", array($this->getId(), $role_id));
            } elseif ($action == "remove") {
                $this->db->execute("DELETE FROM player_roles WHERE user_id = ? AND role_id = ?", array($this->getId(), $role_id));
            } else {
                throw new Exception("Unrecognized role action");
            }

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
        return self::get(self::fetchIdFrom($bzid, "bzid"));
    }

    /**
     * Get a single player by their username
     *
     * @param  string $username The username to look for
     * @return Player
     */
    public static function getFromUsername($username)
    {
        $player = static::get(self::fetchIdFrom($username, 'username'));

        return $player->inject('name', $username);
    }

    /**
     * Get all the players in the database that have an active status
     * @return Player[] An array of player BZIDs
     */
    public static function getPlayers()
    {
        return self::arrayIdToModel(
            self::fetchIdsFrom("status", array("active", "test"), false)
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
     * Count the number of matches a player has participated in
     * @return int
     */
    public function getMatchCount()
    {
        if ($this->cachedMatchCount === null) {
            $this->cachedMatchCount = Match::getQueryBuilder()
                ->active()
                ->with($this)
                ->count();
        }

        return $this->cachedMatchCount;
    }

    /**
     * Get the (victory/total matches) ratio of the player
     * @return float
     */
    public function getMatchWinRatio()
    {
        $count = $this->getMatchCount();

        if ($count == 0) {
            return 0;
        }

        $wins = Match::getQueryBuilder()
            ->active()
            ->with($this, 'win')
            ->count();

        return $wins / $count;
    }

    /**
     * Get the (total caps made by team/total matches) ratio of the player
     * @return float
     */
    public function getMatchAverageCaps()
    {
        $count = $this->getMatchCount();

        if ($count == 0) {
            return 0;
        }

        // Get the sum of team A points if the player was in team A, team B points if the player was in team B
        $query = $this->db->query("
            SELECT
              SUM(
                IF(mp.team_loyalty = 0, team_a_points, team_b_points)
              ) AS sum
            FROM
              matches
            INNER JOIN
              match_participation mp ON mp.match_id = matches.id
            WHERE
              status = 'entered' AND mp.user_id = ?
        ", [$this->id]);

        return $query[0]['sum'] / $count;
    }

    /**
     * Get the match activity in matches per day for a player
     *
     * @return float
     */
    public function getMatchActivity()
    {
        if ($this->matchActivity !== null) {
            return $this->matchActivity;
        }

        $activity = 0.0;

        $matches = Match::getQueryBuilder()
            ->active()
            ->with($this)
            ->where('time')->isAfter(TimeDate::from('45 days ago'))
            ->getModels($fast = true);

        foreach ($matches as $match) {
            $activity += $match->getActivity();
        }

        return $activity;
    }

    /**
     * Return an array of matches this player participated in per month.
     *
     * ```
     * ['yyyy-mm'] = <number of matches>
     * ```
     *
     * @param TimeDate|string $timePeriod
     *
     * @return int[]
     */
    public function getMatchSummary($timePeriod = '1 year ago')
    {
        $since = ($timePeriod instanceof TimeDate) ? $timePeriod : TimeDate::from($timePeriod);

        if (!isset($this->cachedMatchSummary[(string)$timePeriod])) {
            $this->cachedMatchSummary[(string)$timePeriod] = Match::getQueryBuilder()
                ->active()
                ->with($this)
                ->where('time')->isAfter($since)
                ->getSummary($since)
            ;
        }

        return $this->cachedMatchSummary[(string)$timePeriod];
    }

    /**
     * Show the number of messages the user hasn't read yet
     * @return int
     */
    public function countUnreadMessages()
    {
        return $this->fetchCount("WHERE `player` = ? AND `read` = 0",
            $this->id, 'player_conversations'
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
            self::fetchIds("WHERE team = ?", array($teamID))
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getActiveStatuses()
    {
        return array('active', 'reported', 'test');
    }

    /**
     * {@inheritdoc}
     */
    public static function getEagerColumns($prefix = null)
    {
        $columns = [
            'id',
            'bzid',
            'team',
            'username',
            'alias',
            'status',
            'avatar',
            'country',
        ];

        return self::formatColumns($prefix, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public static function getLazyColumns($prefix = null)
    {
        $columns = [
            'email',
            'verified',
            'receives',
            'confirm_code',
            'outdated',
            'description',
            'theme',
            'color_blind_enabled',
            'timezone',
            'joined',
            'last_login',
            'last_match',
            'admin_notes',
        ];

        return self::formatColumns($prefix, $columns);
    }

    /**
     * Get a query builder for players
     * @return PlayerQueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new PlayerQueryBuilder('Player', array(
            'columns' => array(
                'name'     => 'username',
                'team'     => 'team',
                'outdated' => 'outdated',
                'status'   => 'status',
            ),
            'name' => 'name',
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
        ));

        $player->addRole($role_id);
        $player->getIdenticon($player->getId());
        $player->setUsername($username);

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
        $this->db->execute("UPDATE {$this->table} SET outdated = 1 WHERE username = ? AND id != ?", array($username, $this->id));

        if ($username === $this->name) {
            // The player's username hasn't changed, no need to do anything
            return $this;
        }

        // Players who used to have our player's username are not outdated anymore,
        // unless they are more than one.
        // Even though we are sure that the old and new usernames are not equal,
        // MySQL makes a different type of string equality tests, which is why we
        // also check IDs to make sure not to affect our own player's outdatedness.
        $this->db->execute("
            UPDATE {$this->table} SET outdated =
                (SELECT (COUNT(*)>1) FROM (SELECT 1 FROM {$this->table} WHERE username = ? AND id != ?) t)
            WHERE username = ? AND id != ?",
            array($this->name, $this->id, $this->name, $this->id));

        $this->updateProperty($this->name, 'username', $username);
        $this->db->execute("INSERT IGNORE INTO past_callsigns (player, username) VALUES (?, ?)", array($this->id, $username));
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
     * {@inheritdoc}
     * @todo Add a constraint that does this automatically
     */
    public function wipe()
    {
        $this->db->execute("DELETE FROM past_callsigns WHERE player = ?", $this->id);

        parent::wipe();
    }

    /**
     * Find whether the player can delete a model
     *
     * @param  PermissionModel $model       The model that will be seen
     * @param  bool         $showDeleted Whether to show deleted models to admins
     * @return bool
     */
    public function canSee($model, $showDeleted = false)
    {
        return $model->canBeSeenBy($this, $showDeleted);
    }

    /**
     * Find whether the player can delete a model
     *
     * @param  PermissionModel $model The model that will be deleted
     * @param  bool         $hard  Whether to check for hard-delete perms, as opposed
     *                                to soft-delete ones
     * @return bool
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
     * @return bool
     */
    public function canCreate($modelName)
    {
        return $modelName::canBeCreatedBy($this);
    }

    /**
     * Find whether the player can edit a model
     *
     * @param  PermissionModel $model The model which will be edited
     * @return bool
     */
    public function canEdit($model)
    {
        return $model->canBeEditedBy($this);
    }
}
