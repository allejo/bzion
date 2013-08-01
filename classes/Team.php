<?php

class Team extends Controller
{

    /**
     * The name of the team
     * @var string
     */
    private $name;

    /**
     * The description of the team
     * @var string
     */
    private $description;

    /**
     * The url of the team's avatar
     * @var string
     */
    private $avatar;

    /**
     * The creation date of the teamm
     * @var string
     */
    private $created;

    /**
     * The team's current elo
     * @var int
     */
    private $elo;

    /**
     * The team's activity
     * @var double
     */
    private $activity;

    /**
     * The bzid of the team leader
     * @var int
     */
    private $leader;

    /**
     * The number of matches won
     * @var int
     */
    private $matches_won;

    /**
     * The number of matches lost
     * @var int
     */
    private $matches_lost;

    /**
     * The number of matches tied
     * @var int
     */
    private $matches_draw;

    /**
     * The total number of matches
     * @var int
     */
    private $matches_total;

    /**
     * The number of members
     * @var int
     */
    private $members;

    /**
     * The team's status
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "teams";

    /**
     * Construct a new Team
     * @param int $id The team's id
     */
    function __construct($id) {

        parent::__construct($id);
        $team = $this->result;

        $this->name = $team['name'];
        $this->alias = $team['alias'];
        $this->description = $team['description'];
        $this->avatar = $team['avatar'];
        $this->created = new DateTime($team['created']);
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
     * Overload __set to update instance variables and database
     * @param string $name The variable's name
     * @param mixed $value The variable's new value
     */
    function __set($name, $value)
    {
        if ($name == 'elo' || $name == 'leader' || $name == 'matches_draw' || $name == 'matches_lost' || $name == 'matches_won') {
            $type = 'i';
        } else if ($name == 'alias' || $name == 'avatar' || $name == 'description' ||
                   $name == 'message' || $name == 'name' || $name == 'status') {
            $type = 's';
        } else if ($name == 'activity') {
            $type = 'd';
        }

        if (isset($type)) {
            $this->db->query("UPDATE ". $this->table . " SET " . $name . " = ? WHERE id = ?", $type."i", array($value, $this->id));
            $this->{$name} = $value;
        }

        if ($name == 'name') {
            $this->__set('alias', $this->generateAlias($value));
        }

    }

    /**
     * Create a new team
     * @param string $name The name of the team
     * @param int $leader The BZID of the person creating the team, also the leader
     * @param string $avatar The URL to the team's avatar
     * @param string $description The team's description
     * @return Team An object that represents the newly created team
     */
    public static function createTeam($name, $leader, $avatar, $description)
    {
        $alias = Team::generateAlias($name);

        $db = Database::getInstance();

        $query = "INSERT INTO teams VALUES(NULL, ?, ?, ?, ?, NOW(), 1200, 0.00, ?, 0, 0, 0, 1, 'open')";
        $params = array($name, $alias ,$description, $avatar, $leader);

        $db->query($query, "ssssi", $params);
        $id = $db->getInsertId();

        // If the generateAlias() method couldn't find an appropriate alias,
        // just make it the same as the ID
        if ($alias === null) {
            $db->query("UPDATE teams SET alias = id WHERE id = ?", 'i', array($id));
        }


        return new Team($id);
    }

    /**
     * Get the members on the team
     * @param string $select A comma separated list of fields to get
     * @return array The members on the team
     */
    function getMembers($select="*") {
        $members = $this->db->query("SELECT $select FROM players WHERE team = ?", "i", array($this->id));
        return $members;
    }

    /**
     * Get the number of members on the team
     * @return int The number of members on the team
     */
    function getNumMembers() {
        return $this->members;
    }

    /**
     * Get the total number of matches this team has played
     * @return int The total number of matches this team has played
     */
    function getNumTotalMatches() {
        return $this->matches_total;
    }

    /**
     * Get the current elo of the team
     * @return int The elo of the team
     */
    function getElo() {
        return $this->elo;
    }

    /**
     * Get the name of the team
     * @return string The name of the team
     */
    function getName() {
        return $this->name;
    }

    /**
     * Get the activity of the team
     * @return double The team's activity formated to two decimal places
     */
    function getActivity() {
        return sprintf("%.2f", $this->activity);
    }
    
    /**
     * Get the URL that points to the team's page
     * @return string The team's URL, without a trailing slash
     */
    function getURL($dir="teams", $default=NULL) {
        return parent::getURL($dir, $default);
    }

    /**
     * Get the leader of the team
     * @return string The name of the team leader
     */
    function getLeader() {
        $results = $this->db->query("SELECT id,bzid,username FROM players WHERE bzid = ?", "i", array($this->leader));
        return $results[0];
    }

    /**
     * Get the creation date of the team
     * @return string The creation date of the team
     */
    function getCreationDate() {
        return $this->created->format(DATE_FORMAT);
    }

    /**
     * Adds a new member to the team
     * @param int $bzid The bzid of the player to add to the team
     */
    function addMember($bzid) {
        $this->db->query("UPDATE players SET team=? WHERE bzid=?", "ii", array($this->id, $bzid));
        $this->members++;
    }

    /**
     * Get all the teams in the database that are not disabled or deleted
     * @return mixed An array of teams
     */
    public static function getTeams() {
        $db = Database::getInstance();

        $results = $db->query("SELECT id FROM teams WHERE status!=? AND status!=?", "ss", array("disabled", "deleted"));

        return $results;
    }

    /**
     * Gets a team object from the supplied alias
     * @param string $alias The team's alias
     * @return Team The team's id
     */
    public static function getFromAlias($alias) {
        return new Team(self::getIdFrom($alias, "alias"));
    }

}
