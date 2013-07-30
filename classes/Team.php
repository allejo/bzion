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

}
