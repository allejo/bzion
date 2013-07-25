<?php

class Team {

    private $id;
    private $name;
    private $description;
    private $avatar;
    private $created;
    private $elo;
    private $activity;
    private $leader;
    private $mathes_won;
    private $mathes_lost;
    private $mathes_draw;
    private $matches_total;
    private $members;
    private $status;

    private $db;

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

    function members() {
        $members = $this->db->query("SELECT * FROM players WHERE team = ?", "i", array($this->id));
        return $members;
    }

}
