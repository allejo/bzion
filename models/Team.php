<?php
/**
 * This file contains functionality relating to the teams belonging to the current league
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A league team
 * @package    BZiON\Models
 */
class Team extends AvatarModel
{
    /**
     * The description of the team written in markdown
     *
     * @var string
     */
    protected $description_md;

    /**
     * The description of the team parsed to HTML
     *
     * @var string
     */
    protected $description_html;

    /**
     * The creation date of the team
     *
     * @var TimeDate
     */
    protected $created;

    /**
     * The team's current elo
     *
     * @var int
     */
    protected $elo;

    /**
     * The team's activity
     *
     * @var double
     */
    protected $activity;

    /**
     * The id of the team leader
     *
     * @var int
     */
    protected $leader;

    /**
     * The number of matches won
     *
     * @var int
     */
    protected $matches_won;

    /**
     * The number of matches lost
     *
     * @var int
     */
    protected $matches_lost;

    /**
     * The number of matches tied
     *
     * @var int
     */
    protected $matches_draw;

    /**
     * The total number of matches
     *
     * @var int
     */
    protected $matches_total;

    /**
     * The number of members
     *
     * @var int
     */
    protected $members;

    /**
     * The team's status
     *
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "teams";

    /**
     * The location where avatars will be stored
     */
    const AVATAR_LOCATION = "/web/assets/imgs/avatars/teams/";

    const CREATE_PERMISSION = Permission::CREATE_TEAM;
    const EDIT_PERMISSION = Permission::EDIT_TEAM;
    const SOFT_DELETE_PERMISSION = Permission::SOFT_DELETE_TEAM;
    const HARD_DELETE_PERMISSION = Permission::HARD_DELETE_TEAM;

    /**
     * {@inheritDoc}
     */
    protected function assignResult($team)
    {
        $this->name = $team['name'];
        $this->alias = $team['alias'];
        $this->description_md = $team['description_md'];
        $this->description_html = $team['description_html'];
        $this->avatar = $team['avatar'];
        $this->created = new TimeDate($team['created']);
        $this->elo = $team['elo'];
        $this->activity = $team['activity'];
        $this->leader = $team['leader'];
        $this->matches_won = $team['matches_won'];
        $this->matches_lost = $team['matches_lost'];
        $this->matches_draw = $team['matches_draw'];
        $this->members = $team['members'];
        $this->status = $team['status'];

        $this->matches_total = $this->matches_won + $this->matches_lost + $this->matches_draw;
    }

    /**
     * Adds a new member to the team
     *
     * @param int $id The id of the player to add to the team
     *
     * @return boolean|null True if both the player was added to the team AND the team member count was incremented
     */
    public function addMember($id)
    {
        $player = new Player($id);

        if (!$player->isTeamless())
            throw new Exception("The player already belongs in a team");

        $player->setTeam($this->getId());
        $this->update('members', ++$this->members, "i");

    }

    /**
     * Increase or decrease the ELO of the team
     *
     * @param int $adjust The value to be added to the current ELO (negative to substract)
     */
    public function changeElo($adjust)
    {
        $this->elo += $adjust;
        $this->update("elo", $this->elo, "i");
    }

    /**
     * Increment the team's match count
     *
     * @param int    $adjust The number to add to the current matches number (negative to substract)
     * @param string $type   The match count that should be changed. Can be 'win', 'draw' or 'loss'
     */
    public function changeMatchCount($adjust, $type)
    {
        $this->matches_total += $adjust;

        switch ($type) {
            case "win":
            case "won":
                $this->update("matches_won", $this->matches_won += $adjust, "i");

                return;
            case "loss":
            case "lost":
                $this->update("matches_lost", $this->matches_lost += $adjust, "i");

                return;
            default:
                $this->update("matches_draw", $this->matches_draw += $adjust, "i");

                return;
        }
    }

    /**
     * Decrement the team's match count by one
     *
     * @param string $type The type of the match. Can be 'win', 'draw' or 'loss'
     */
    public function decrementMatchCount($type)
    {
        $this->changeMatchCount(-1, $type);
    }

    /**
     * Get the activity of the team
     *
     * @return string The team's activity formated to two decimal places
     */
    public function getActivity()
    {
        return sprintf("%.2f", $this->activity);
    }

    /**
     * Get the creation date of the team
     *
     * @return string The creation date of the team
     */
    public function getCreationDate()
    {
        return $this->created->diffForHumans();
    }

    /**
     * Get the creation date for the team in a specified format
     *
     * @param string $format The format of the data
     *
     * @return string
     */
    public function getCreationDateFormat($format = "Y-m-d")
    {
        return $this->created->format($format);
    }

    /**
     * Get the description of the team
     *
     * @param  boolean $md false for HTML format, true for the original markdown
     * @return string  The description of the team
     */
    public function getDescription($md=false)
    {
        return ($md) ? $this->description_md : $this->description_html;
    }

    /**
     * Get the current elo of the team
     *
     * @return int The elo of the team
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * Get the leader of the team
     *
     * @return Player The object representing the team leader
     */
    public function getLeader()
    {
        return new Player($this->leader);
    }

    /**
     * Get the matches this team has participated in
     *
     * @param string $matchType The filter for match types: "all", "wins", "losses", or "draws"
     * @param int    $count     The amount of matches to be retrieved
     * @param int    $page      The number of the page to return
     *
     * @return Match[] The array of match IDs this team has participated in
     */
    public function getMatches($matchType = "all", $count = 5, $page = 1)
    {
        return Match::getQueryBuilder()
             ->active()
             ->with($this, $matchType)
             ->sortBy('time')->reverse()
             ->limit($count)->fromPage($page)
             ->getModels();
    }

    /**
     * Get the number of matches that resulted as a draw
     *
     * @return int The number of matches, respectively
     */
    public function getMatchesDraw()
    {
        return $this->matches_draw;
    }

    /**
     * Get the number of matches that the team has lost
     *
     * @return int The number of matches, respectively
     */
    public function getMatchesLost()
    {
        return $this->matches_lost;
    }

    /**
     * Get the URL that points to the team's list of matches
     *
     * @return string The team's list of matches
     */
    public function getMatchesURL()
    {
        return Service::getGenerator()->generate("match_by_team_list", array("team" => $this->getAlias()));
    }

    /**
     * Get the number of matches that the team has won
     *
     * @return int The number of matches, respectively
     */
    public function getMatchesWon()
    {
        return $this->matches_won;
    }

    /**
     * Get the members on the team
     *
     * @return Player[] The members on the team
     */
    public function getMembers()
    {
        $leader = $this->leader;
        $members = Player::getTeamMembers($this->id);

        usort($members, function (&$a, &$b) use ($leader) {
            // Leader always goes first
            if ($a->getId() == $leader)
                return -1;
            if ($b->getId() == $leader)
                return 1;

            // Sort the rest of the players alphabetically
            $sort = Player::getAlphabeticalSort();

            return $sort($a, $b);
        });

        return $members;
    }

    /**
     * Get the name of the team
     *
     * @return string The name of the team
     */
    public function getName()
    {
        if (!$this->valid)
            return "None";
        return $this->name;
    }

    /**
     * Get the name of the team, safe for use in your HTML
     *
     * @return string The name of the team
     */
    public function getEscapedName()
    {
        if (!$this->valid)
            return "<em>None</em>";
        return $this->escape($this->name);
    }

    /**
     * Get the number of members on the team
     *
     * @return int The number of members on the team
     */
    public function getNumMembers()
    {
        return $this->members;
    }

    /**
     * Get the total number of matches this team has played
     *
     * @return int The total number of matches this team has played
     */
    public function getNumTotalMatches()
    {
        return $this->matches_total;
    }

    /**
     * Get the rank category a team belongs too based on their ELO
     *
     * This value is always a multiple of 100 and less than or equal to 2000
     *
     * @return int The rank category a team belongs to
     */
    public function getRankValue()
    {
        return min(2000, floor($this->getElo() / 100) * 100);
    }

    /**
     * Get the HTML for an image with the rank symbol
     *
     * @return string The HTML for a rank image
     */
    public function getRankImageLiteral()
    {
        return '<div class="rank lvl-' . $this->getRankValue() . '"></div>';
    }

    /**
     * Increment the team's match count by one
     *
     * @param string $type The type of the match. Can be 'win', 'draw' or 'loss'
     */
    public function incrementMatchCount($type)
    {
        $this->changeMatchCount(1, $type);
    }

    /**
     * Check if a player is part of this team
     *
     * @param int $playerID The player to check
     *
     * @return bool True if the player belongs to this team
     */
    public function isMember($playerID)
    {
        $player = new Player($playerID);

        return ($player->getTeam()->getId() == $this->getId());
    }

    /**
     * Removes a member from the team
     *
     * @param  int  $id The id of the player to remove
     * @return void
     */
    public function removeMember($id)
    {
        if (!$this->isMember($id))
            throw new Exception("The player is not a member of that team");

        $player = new Player($id);

        $player->update("team", NULL, "s");
        $this->update('members', --$this->members, "i");
    }

    /**
     * Update the description of the team
     *
     * @param  string $description_md The description of the team written as markdown
     * @return void
     */
    public function setDescription($description_md)
    {
        $this->update("description_md", $description_md, "s");
        $this->update("description_html", parent::mdTransform($description_md), "s");
    }

    /**
     * Change the status of the team
     *
     * @param  string $newStatus The new status of the team (open, closed, disabled or deleted)
     * @return self
     */
    public function setStatus($newStatus)
    {
        return $this->updateProperty($this->status, 'status', $newStatus, 's');
    }

    /**
     * Change the leader of the team
     *
     * @param  int  $leader The ID of the new leader of the team
     * @return self
     */
    public function setLeader($leader)
    {
        return $this->updateProperty($this->leader, 'leader', $leader, 'i');
    }

    /**
     * Find if a specific match is the team's last one
     *
     * @param  int  $matchID The ID of the match
     * @return bool
     */
    public function isLastMatch($matchID)
    {
        // Find if this team participated in any matches after the current match
        return !Match::getQueryBuilder()
            ->with($this)
            ->active()
            ->sortBy('id')->reverse()
            ->startAt($matchID)
            ->any();
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        parent::delete();

        // Remove all the members of a deleted team
        $this->updateProperty($this->members, 'members', 0, 'i');
        $this->db->query("UPDATE `players` SET `team` = NULL WHERE `team` = ?",
            'i', $this->id);
    }

    /**
     * Create a new team
     *
     * @param  string $name        The name of the team
     * @param  int    $leader      The ID of the person creating the team, also the leader
     * @param  string $avatar      The URL to the team's avatar
     * @param  string $description The team's description
     * @param  string $status      The team's status (open, closed, disabled or deleted)
     * @return Team   An object that represents the newly created team
     */
    public static function createTeam($name, $leader, $avatar, $description, $status='closed')
    {
        $team = self::create(array(
            'name' => $name,
            'alias' => self::generateAlias($name),
            'description_md' => $description,
            'description_html' => parent::mdTransform($description),
            'elo' => 1200,
            'activity' => 0.00,
            'matches_won' => 0,
            'matches_draw' => 0,
            'matches_lost' => 0,
            'members' => 0,
            'avatar' => $avatar,
            'leader' => $leader,
            'status' => $status
        ), 'ssssidiiiisss', 'created');

        $team->addMember($leader);
        $team->getIdenticon($team->getId());

        return $team;
    }

    /**
     * Get all the teams in the database that are not disabled or deleted
     *
     * @return Team[] An array of Team IDs
     */
    public static function getTeams()
    {
        return self::arrayIdToModel(
            parent::fetchIdsFrom(
                "status", array("disabled", "deleted"),
                "s", true, "ORDER BY elo DESC"
            )
        );
    }

    /**
     * Get a single team by its name
     *
     * @param  string $name The team name to look for
     * @return Team
     */
    public static function getFromName($name)
    {
        return new Team(self::fetchIdFrom($name, 'name', 's'));
    }

    /**
     * {@inheritDoc}
     */
    public static function getActiveStatuses()
    {
        return array('open', 'closed');
    }

    /**
     * Get a query builder for teams
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new QueryBuilder('Team', array(
            'columns' => array(
                'name'   => 'name',
                'status' => 'status'
            ),
            'name' => 'name',
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function isEditor($player)
    {
        return $player->getId() == $this->getLeader()->getId();
    }
}
