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

class team
{
	public $name;
	public $score;
	public $leader_id;
	public $leader_callsign;
	public $matches_total;
	public $matches_won;
	public $matches_tied;
	public $matches_lost;
	public $description;
	public $logo;
	
	public $dbc;
	public $teamID;
	
    function __construct($id)
	{
		$this->teamID = $id;
	    $this->dbc = new database();
	    
	    $results = $this->dbc->query("SELECT bzion_teams.*, bzion_players.callsign FROM bzion_teams JOIN bzion_players ON bzion_players.uID = bzion_teams.leaderID WHERE tID = '" . $id . "'");
	    
	    $this->name = $results['name'];
	    $this->leader_id = $results['leaderID'];
	    $this->leader_callsign = $results['callsign'];
	    $this->description = $results['description'];
	    $this->logo = $results['logo'];
	    $this->score = $results['score'];
	    $this->activity = $results['activity'];
	    $this->matches_won = $results['wins'];
	    $this->matches_tied = $results['ties'];
	    $this->matches_lost = $results['losses'];
	    $this->matches_total = $results['total'];
	}
	
	function getMembers()
	{
		$team_members = $this->dbc->query("SELECT uID, callsign, (SELECT name FROM bzion_countries WHERE bzion_players.location = bzion_countries.id) AS country FROM bzion_players WHERE teamID = '" . $this->teamID . "'");
		
		return $team_members;
	}
}