<?php

class Match {

    private $id;
    private $team_a;
    private $team_b;
    private $team_a_points;
    private $team_b_points;
    private $team_a_elo_new;
    private $team_b_elo_new;
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

        $results = $this->db->query("SELECT * FROM matches WHERE id = ?", "i", array($id));
        $match = $results[0];

        $this->team_a = $match['team_a'];
        $this->team_b = $match['team_b'];
        $this->team_a_points = $match['team_a_points'];
        $this->team_b_points = $match['team_b_points'];
        $this->team_a_elo_new = $match['team_a_elo_new'];
        $this->team_b_elo_new = $match['team_b_elo_new'];
        $this->elo_diff = $match['elo_diff'];
        $this->timestamp = new DateTime($match['timestamp']);
        $this->updated = new DateTime($match['updated']);
        $this->duration = $match['duration'];
        $this->entered_by = $match['entered_by'];
        $this->status = $match['status'];

    }

    public static function enterMatch($a, $b, $a_points, $b_points, $duration, $entered_by, $timestamp = "now") {

        $result = $this->db->query("SELECT elo FROM teams WHERE id = ?", "i", array($a));
        $a_elo = $result['elo'];
        $result = $this->db->query("SELECT elo FROM teams WHERE id = ?", "i", array($b));
        $b_elo = $result['elo'];

        $diff = calculateEloDiff($a_elo, $b_elo, $team_a_points, $team_a_points, $duration);

        $a_elo += $diff;
        $b_elo -= $diff;
        $diff = abs($diff);

        $timestamp = new DateTime($timestamp);

        $db = new Database();
        $results = $db->query("INSERT INTO matches (team_a, team_b, team_a_points, team_b_points, team_a_elo_new, team_b_elo_new, elo_diff, timestamp, updated, duration, entered_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
        "iiiiiiisiii", array($a, $b, $a_points, $b_points, $a_elo, $b_elo, $diff, $timestamp, $duration, $entered_by, 0));

        return new Match($db->getInsertId());
    }

    function calculateEloDiff($a_elo, $b_elo, $a_points, $b_points, $duration) {
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
