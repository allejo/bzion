<?php

class TeamController extends HTMLController {

    public function showAction(Team $team) {
        if ($this->validate($team))
            return array("team" => $team);
    }

    public function listAction() {
        return array("teams" => Team::getTeams());
    }
}
