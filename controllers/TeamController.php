<?php

use Symfony\Component\HttpFoundation\RedirectResponse;

class TeamController extends HTMLController
{
    public function showAction(Team $team)
    {
        return array("team" => $team);
    }

    public function listAction()
    {
        return array("teams" => Team::getTeams());
    }

    public function removeAction(Team $team, Player $me)
    {
        if (!$me->hasPermission(Permission::SOFT_DELETE_TEAM)) {
            return new RedirectResponse(Service::getGenerator()->generate('team_list'));
        }

        $team->delete();

        return new RedirectResponse(Service::getGenerator()->generate('team_list'));
    }
}
