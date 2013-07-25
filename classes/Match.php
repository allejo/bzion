<?php

class Match {

    private $id;
    private $team_a;
    private $team_b;
    private $team_a_points;
    private $team_b_points;
    private $team_a_elo;
    private $team_b_elo;
    private $elo_diff;
    private $timestamp;
    private $updated;
    private $duration;
    private $entered_by;
    private $status;

    private $db;

    function __construct($id) {

        $this->db = new Database();
        $this->id = $id;

        $results = $this->db->query("SELECT * FROM matches WHERE bzid = ?", "i", array($id));

        $this->team_a = $results['team_a'];
        $this->team_b = $results['team_b'];
        $this->team_a_points = $results['team_a_points'];
        $this->team_b_points = $results['team_b_points'];
        $this->team_a_elo = $results['team_a_elo'];
        $this->team_b_elo = $results['team_b_elo'];
        $this->elo_diff = $results['elo_diff'];
        $this->timestamp = new DateTime($results['timestamp']);
        $this->updated = new DateTime($results['updated']);
        $this->duration = $results['duration'];
        $this->entered_by = $results['entered_by'];
        $this->status = $results['status'];

    }

    public static function enter_match($a, $b, $a_points, $b_points, $duration, $entered_by, $timestamp = "now") {

        $result = $this->db->query("SELECT elo FROM teams WHERE id = ?", "i", array($a));
        $a_elo = $result['elo'];
        $result = $this->db->query("SELECT elo FROM teams WHERE id = ?", "i", array($b));
        $b_elo = $result['elo'];

        $diff = calculate_elo_diff($a_elo, $b_elo, $team_a_points, $team_a_points, $duration);

        $a_elo += $diff;
        $b_elo -= $diff;
        $diff = abs($diff);

        $timestamp = new DateTime($timestamp);
        
        $results = $this->db->query("INSERT INTO matches (team_a, team_b, team_a_points, team_b_points, team_a_elo, team_b_elo, elo_diff, timestamp, updated, duration, entered_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        "iiiiiiissiii", array($a, $b, $a_points, $b_points, $a_elo, $b_elo, $diff, $timestamp, $timestamp, $duration, $entered_by, 0));
    }

    function calculate_elo_diff($a_elo, $b_elo, $a_points, $b_points, $duration) {
        $prob = 1.0 / (1 + 10 ^ (($team_b-$team_a)/400.0));
        if ($a_points > $b_points) {
           $diff = 50*(1-$prob);
        } else if ($a_points == $b_points) {
            $diff = 50*(0.5-$prob);
        } else {
            $diff = 50*(0-$prob);
        }

        if ($duration == 20) {
            return floor((2/3)*$diff);
        }

        return floor($diff);
    }

}
