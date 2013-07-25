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

	}

	function total_matches() {
		return ($this->matches_won + $this->matches_lost + $this->matches_draw);
	}

	function members() {
		$members = $this->db->query("SELECT * FROM users WHERE team = ?", "i", array($this->id));
		return $members;
	}

}
