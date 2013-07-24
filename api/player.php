<?php
	/*
		Copyright 2013 Ashvala Vinay and Vladimir Jimenez
		
		Permission is hereby granted, free of charge, to any person obtaining
		a copy of this software and associated documentation files (the
		"Software"), to deal in the Software without restriction, including
		without limitation the rights to use, copy, modify, merge, publish,
		distribute, sublicense, and/or sell copies of the Software, and to
		permit persons to whom the Software is furnished to do so, subject to
		the following conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
		MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
		LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
		OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
		WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	*/
	
include('database.php');
	
class player
{
	public $active;
	public $banned;
	public $bzID;
	public $callsign;
	public $country;
	public $join_date;
	public $last_login;
	public $teamID;
	public $teamName;
	
	public $dbc;
	
    function __construct($type, $id)
    {
	    $this->dbc = new database();
	    
	    if ($type == "bzid")
		    $results = $this->dbc->query("SELECT bzion_players.*, (SELECT name FROM bzion_countries WHERE bzion_players.location = bzion_countries.id) AS country, IF(bzion_players.teamID = 0, 'Teamless', bzion_teams.name) AS teamName FROM bzion_players LEFT JOIN bzion_teams ON bzion_players.teamID = bzion_teams.tID WHERE bzion_players.bzID = '" . $id . "'");
		else
		    $results = $this->dbc->query("SELECT bzion_players.*, (SELECT name FROM bzion_countries WHERE bzion_players.location = bzion_countries.id) AS country, IF(bzion_players.teamID = 0, 'Teamless', bzion_teams.name) AS teamName FROM bzion_players LEFT JOIN bzion_teams ON bzion_players.teamID = bzion_teams.tID WHERE bzion_players.uID = '" . $id . "'");
		    
		$joined_date = new DateTime($results['join_date']);
		    
		$this->active = $results['active'];
		$this->banned = $results['banned'];
		$this->bzID = $results['bzID'];
		$this->callsign = $results['callsign'];
		$this->country = $results['country'];
		$this->join_date = $joined_date->format('Y-m-d');
		$this->last_login = $results['last_login'];
		$this->teamID = $results['teamID'];
		$this->teamName = $results['teamName'];
    }
}
	
	