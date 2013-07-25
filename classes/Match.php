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

}
