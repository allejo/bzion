<?php

class Team {

    /**
     * The id of the team
     * @var int
     */
    private $id;

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
     * The database variable used for queries
     * @var MySQLi
     */
    private $db;

    /**
     * Construct a new Team
     * @param int $id The team's id
     */
    function __construct($id) {

        $this->db = new Database();
        $this->id = $id;

        $results = $this->db->query("SELECT * FROM teams WHERE id = ?", "i", array($id));

        $this->name = $results['name'];
        $this->description = $results['description'];
        $this->avatar = $results['avatar'];
        $this->created = new DateTime($results['created']);
        $this->elo = $reults['elo'];
        $this->activity = $results['activity'];
        $this->leader = $result['leader'];
        $this->matches_won = $results['matches_won'];
        $this->matches_lost = $results['matches_lost'];
        $this->matches_draw = $results['matches_draw'];
        $this->members = $results['members'];
        $this->status = $results['status'];

        $this->matches_total = $this->matches_won + $this->matches_lost + $this->matches_draw;

    }

    /**
     * Overload __set to update instance variables and database
     * @param string $name The variable's name
     * @param mixed $value The variable's new value
     */
    function __set($name, $value)
    {
        switch ($name)
        {
            case 'activity':
            {
                $this->db->query("UPDATE teams SET activity = ? WHERE id = ?", "di", array($value, $this->id));
                $this->activity = $value;
            }
            break;

            case 'avatar':
            {
                $this->db->query("UPDATE teams SET avatar = ? WHERE id = ?", "si", array($value, $this->id));
                $this->avatar = $value;
            }
            break;

            case 'description':
            {
                $this->db->query("UPDATE teams SET description = ? WHERE id = ?", "si", array($value, $this->id));
                $this->description = $value;
            }
            break;

            case 'elo':
            {
                $this->db->query("UPDATE teams SET elo = ? WHERE id = ?", "ii", array($value, $this->id));
                $this->elo = $value;
            }
            break;

            case 'leader':
            {
                $this->db->query("UPDATE teams SET leader = ? WHERE id = ?", "ii", array($value, $this->id));
                $this->leader = $value;
            }
            break;

            case 'matches_draw':
            {
                $this->db->query("UPDATE teams SET matches_draw = ? WHERE id = ?", "ii", array($this->matches_draw, $this->id));
                $this->matches_total += $value - $this->matches_draw;
                $this->matches_draw = $value;
            }

            case 'matches_lost':
            {
                $this->db->query("UPDATE teams SET matches_lost = ? WHERE id = ?", "ii", array($this->matches_lost, $this->id));
                $this->matches_total += $value - $this->matches_lost;
                $this->matches_lost = $value;
            }

            case 'matches_won':
            {
                $this->db->query("UPDATE teams SET matches_won = ? WHERE id = ?", "ii", array($this->matches_won, $this->id));
                $this->matches_total += $value - $this->matches_won;
                $this->matches_won = $value;
            }

            case 'name':
            {
                $this->db->query("UPDATE teams SET name = ? WHERE id = ?", "si", array($value, $this->id));
                $this->name = $value;
            }
            break;

            case 'status':
            {
                $this->db->query("UPDATE teams SET status = ? WHERE id = ?", "si", array($value, $this->id));
                $this->status = $value;
            }
            break;
        }
    }

    /**
     * Create a new team
     * @param string $name The name of the team
     * @param int $leader The BZID of the person creating the team, also the leader
     * @param string $avatar The URL to the team's avatar
     * @param string $description The team's description
     */
    public static function createTeam($name, $leader, $avatar, $description)
    {
        $query = "INSERT INTO teams VALUES(NULL, ?, ?, ?, NOW(), 1200, 0.00, ?, 0, 0, 0, 1, 'open')";
        $params = array($name, $description, $avatar, $leader);

        $db = new Database();
        $db->query($query, "sssi", $params);
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
