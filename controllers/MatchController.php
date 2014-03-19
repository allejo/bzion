<?php

class MatchController extends HTMLController {

    public function cleanup() {
        $footer = new Footer();

        $footer->addScript("assets/js/matches.js");

        $footer->draw();
    }

    public function listByTeamAction(Team $team) {
        return $this->render("Match/list.html.twig",
               array ("matches" => $team->getMatches(), "team" => $team));
    }

    public function listAction() {
        return array("matches" => Match::getMatches());
    }
}

