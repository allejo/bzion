<?php

class Player {

	private $id;
	private $bzid;
	private $team;
	private $username;
	private $status;
	private $access;
	private $avatar;
	private $description;
	private $country;
	private $timezone;
	private $joined;
	private $last_login;

	private $db;

	function __construct($bzid) {

		$this->db = new Database();
		$this->bzid = $bzid;

		$results = $this->db->query("SELECT * FROM players WHERE bzid = ?", "i", array($bzid));

		$this->id = $results['id'];
		$this->username = $results['username'];
		$this->status = $results['status'];
		$this->access = $results['access'];
		$this->avatar = $results['avatar'];
		$this->description = $results['description'];
		$this->country = $results['country'];
		$this->timezone = $results['timezone'];
		$this->joined = new DateTime($results['joined']);
		$this->last_login = new DateTime($results['last_login']);

	}

}
