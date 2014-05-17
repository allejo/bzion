<?php

class MatchController extends HTMLController
{
    public function listByTeamAction($alias)
    {
        $team = Team::getTeamByAlias($alias);

        return $this->render("Match/list.html.twig",
               array ("matches" => $team->getMatches(), "team" => $team));
    }

    public function listByTeamSortAction($alias, $type)
    {
        $team = Team::getTeamByAlias($alias);

        return $this->render("Match/list.html.twig",
            array ("matches" => $team->getMatches($type, 50), "team" => $team));
    }

    public function listAction()
    {
        return array("matches" => Match::getMatches());
    }
}
