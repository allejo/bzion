<?php

class Team extends Controller
{

    /**
     * The name of the team
     * @var string
     */
    private $name;

    /**
     * The unique URL-friendly identifier of the team
     * @var string
     */
    private $alias;

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
    private $mathes_won;

    /**
     * The number of matches lost
     * @var int
     */
    private $mathes_lost;

    /**
     * The number of matches tied
     * @var int
     */
    private $mathes_draw;

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
     * Construct a new Team
     * @param int $id The team's id
     */
    function __construct($id) {

        parent::__construct($id, "teams");
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
        $table = "teams";

        if ($name == 'elo' || $name == 'leader' || $name == 'matches_draw' || $name == 'matches_lost' || $name == 'matches_won') {
            $type = 'i';
        } else if ($name == 'alias' || $name == 'avatar' || $name == 'description' ||
                   $name == 'message' || $name == 'name' || $name == 'status') {
            $type = 's';
        } else if ($name == 'activity') {
            $type = 'd';
        }

        if (isset($type)) {
            $this->db->query("UPDATE ". $table . " SET " . $name . " = ? WHERE id = ?", $type."i", array($value, $this->id));
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
     * @return array The members on the team
     */
    function members() {
        $members = $this->db->query("SELECT * FROM players WHERE team = ?", "i", array($this->id));
        return $members;
    }

<<<<<<< HEAD
    /**
     * Generate a URL-friendly unique alias for a team name
     *
     * @param string $name The original team name
     * @return string|Null The generated alias, or Null if we couldn't make one
     * @todo Make this method more general, so it can be used for pages as well?
     */
    static function generateAlias($team) {
        // Convert team name to lowercase
        $team = strtolower($team);

        // List of characters which should be converted to dashes
        $makeDash = array(' ', '_');

        $team = str_replace($makeDash, '-', $team);

        // Only keep letters, numbers and dashes - delete everything else
        $team = preg_replace("/[^a-zA-Z\-0-9]+/", "", $team);

        if (str_replace('-', '', $team) == '') {
            // The team name only contains symbols or Unicode characters!
            // This means we can't convert it to an alias
            return null;
        }

        // An alias name can't only contain numbers, because it will be
        // indistinguishable from an ID. If it does, add a dash in the end.
        if (preg_match("/^[0-9]+$/", $team)) {
            $team = $team . '-';
        }

        // Try to find duplicates
        $db = Database::getInstance();
        $result = $db->query("SELECT alias FROM teams WHERE alias REGEXP ?", 's', array("^".$team."[0-9]*$"));

        // The functionality of the following code block is provided in PHP 5.5's
        // array_column function. What is does is convert the multi-dimensional
        // array that $db->query() gave us into a single-dimensional one.
        $aliases = array();
        if (is_array($result)) {
            foreach ($result as $r) {
                $aliases[] = $r['alias'];
            }
        }

        if (!in_array($team, $aliases))
            return $team;

        // If there's already a team with the alias we generated, put a number
        // in the end of it and keep incrementing it until there is we find
        // an open spot.
        $i = 2;
        while(in_array($team.$i, $aliases)) {
            $i++;
        }

        return $team.$i;
    }

    /**
     * Get all the teams in the database that have are not disabled or deleted
     * @return mixed An array of teams
     */
    public static function getTeams() {
        $db = Database::getInstance();

        $results = $db->query("SELECT * FROM teams WHERE status!=? AND status!=?", "ss", array("disabled", "deleted"));

        return $results;
    }

=======
>>>>>>> f51ac14aee4d4af9da1496e1e8291dd22a8592d1
}
