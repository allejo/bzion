<?php

/**
 * A league team
 */
class Team extends AliasModel
{

    /**
     * The name of the team
     *
     * @var string
     */
    private $name;

    /**
     * The description of the team
     *
     * @var string
     */
    private $description;

    /**
     * The url of the team's avatar
     *
     * @var string
     */
    private $avatar;

    /**
     * The creation date of the teamm
     *
     * @var TimeDate
     */
    private $created;

    /**
     * The team's current elo
     *
     * @var int
     */
    private $elo;

    /**
     * The team's activity
     *
     * @var double
     */
    private $activity;

    /**
     * The id of the team leader
     *
     * @var int
     */
    private $leader;

    /**
     * The number of matches won
     *
     * @var int
     */
    private $matches_won;

    /**
     * The number of matches lost
     *
     * @var int
     */
    private $matches_lost;

    /**
     * The number of matches tied
     *
     * @var int
     */
    private $matches_draw;

    /**
     * The total number of matches
     *
     * @var int
     */
    private $matches_total;

    /**
     * The number of members
     *
     * @var int
     */
    private $members;

    /**
     * The team's status
     *
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "teams";

    /**
     * Construct a new Team
     *
     * @param int $id The team's id
     */
    public function __construct($id) {
        parent::__construct($id);
        if (!$this->valid)
            return;

        $team = $this->result;

        $this->name = $team['name'];
        $this->alias = $team['alias'];
        $this->description = $team['description'];
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
     * Create a new team
     *
     * @param string $name The name of the team
     * @param int $leader The ID of the person creating the team, also the leader
     * @param string $avatar The URL to the team's avatar
     * @param string $description The team's description
     * @return Team An object that represents the newly created team
     */
    public static function createTeam($name, $leader, $avatar, $description) {
        $alias = self::generateAlias($name);

        $db = Database::getInstance();

        $query = "INSERT INTO teams VALUES(NULL, ?, ?, ?, ?, NOW(), 1200, 0.00, ?, 0, 0, 0, 0, 'open')";
        $params = array(
            $name,
            $alias,
            $description,
            $avatar,
            $leader
        );

        $db->query($query, "ssssi", $params);
        $id = $db->getInsertId();

        // If the generateAlias() method couldn't find an appropriate alias,
        // just make it the same as the ID
        if ($alias === null) {
            $db->query("UPDATE teams SET alias = id WHERE id = ?", 'i', array(
                $id
            ));
        }

        $team = new Team($id);

        $team->addMember($leader);

        return $team;
    }

    /**
     * Get the members on the team
     *
     * @return Player[] The members on the team
     */
    public function getMembers() {
        return Player::getTeamMembers($this->id);
    }

    /**
     * Get the number of members on the team
     *
     * @return int The number of members on the team
     */
    public function getNumMembers() {
        return $this->members;
    }

    /**
     * Get the total number of matches this team has played
     *
     * @return int The total number of matches this team has played
     */
    public function getNumTotalMatches() {
        return $this->matches_total;
    }

    /**
     * Increment the team's match count by one
     *
     * @param string $type The type of the match. Can be 'win', 'draw' or 'loss'
     */
    public function incrementMatchCount($type) {
        $this->changeMatchCount(1, $type);
    }

    /**
     * Decrement the team's match count by one
     *
     * @param string $type The type of the match. Can be 'win', 'draw' or 'loss'
     */
    public function decrementMatchCount($type) {
        $this->changeMatchCount(-1, $type);
    }

    /**
     * Increment the team's match count
     *
     * @param int $adjust The number to add to the current matches number (negative to substract)
     * @param string $type The match count that should be changed. Can be 'win', 'draw' or 'loss'
     */
    public function changeMatchCount($adjust, $type) {
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
     * Get the current elo of the team
     *
     * @return int The elo of the team
     */
    public function getElo() {
        return $this->elo;
    }

    /**
     * Increase or decrease the ELO of the team
     *
     * @param int $adjust The value to be added to the current ELO (negative to substract)
     */
    public function changeElo($adjust) {
        $this->elo += $adjust;
        $this->update("elo", $this->elo, "i");
    }

    /**
     * Get the name of the team
     *
     * @return string The name of the team
     */
    public function getName() {
        if (!$this->valid)
            return "<em>None</em>";
        return $this->name;
    }

    /**
     * Get the activity of the team
     *
     * @return string The team's activity formated to two decimal places
     */
    public function getActivity() {
        return sprintf("%.2f", $this->activity);
    }

    /**
     * Get the URL that points to the team's list of matches
     *
     * @return string The team's list of matches
     */
    public function getMatchesURL() {
        return Service::getGenerator()->generate("match_by_team_list", array("team" => $this->getAlias()));
    }

    /**
     * Get the leader of the team
     *
     * @return Player The object representing the team leader
     */
    public function getLeader() {
        return new Player($this->leader);
    }

    /**
     * Get the creation date of the team
     *
     * @return string The creation date of the team
     */
    public function getCreationDate() {
        return $this->created->diffForHumans();
    }

    /**
     * Generate the HTML for a hyperlink to link to a team's profile
     * @return string The HTML hyperlink to a team's profile
     */
    public function getLinkLiteral() {
        return '<a href="' . $this->getURL() . '">' . $this->getName() . '</a>';
    }

    /**
     * Adds a new member to the team
     *
     * @param int $id The id of the player to add to the team
     */
    public function addMember($id) {
        $this->db->query("UPDATE players SET team=? WHERE id=?", "ii", array(
            $this->id,
            $id
        ));
        $this->update('members', ++$this->members, "i");
    }

    /**
     * Removes a member from the team
     *
     * *Warning*: This method does not check whether the player is already
     * a member of the team
     *
     * @param int $id The id of the player to remove
     */
    public function removeMember($id) {
        $this->db->query("UPDATE players SET team=0 WHERE id=?", "i", array(
            $id
        ));
        $this->update('members', --$this->members, "i");
    }

    /**
     * Get the matches this team has participated in
     *
     * @return Match[] The array of match IDs this team has participated in
     */
    public function getMatches() {
        return Match::getMatchesByTeam($this->id);
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
}
