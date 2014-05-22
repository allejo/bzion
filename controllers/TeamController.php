<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\FormFactory;

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

    public function deleteAction(Team $team, Player $me, Session $session)
    {
        if (!$me->hasPermission(Permission::SOFT_DELETE_TEAM)) {
            throw new ForbiddenException("You are not allowed to delete a team");
        }

        return $this->showConfirmationForm(function() use(&$team, &$session) {
            $team->delete();
            $session->getFlashBag()->add('success',
                     "The team {$team->getName()} was deleted successfully");

            return new RedirectResponse(Service::getGenerator()->generate('team_list'));
        }, null, array('team' => $team));
    }

    public function kickAction(Team $team, Player $player, Player $me, Session $session)
    {
        if (!$me->hasPermission(Permission::EDIT_TEAM) &&
            $team->getLeader()->getId() != $me->getId())
            throw new ForbiddenException("You are not allowed to kick a player off that team!");

        if ($team->getLeader()->getId() == $player->getId())
            throw new ForbiddenException("You can't kick the leader off their team.");

        if (!$team->isMember($player->getId()))
            throw new ForbiddenException("The specified player is not a member of that team.");

        return $this->showConfirmationForm(function() use(&$team, &$player, &$session) {
            $team->removeMember($player->getId());

            $message = "Player {$player->getUsername()} has been kicked from {$team->getName()}";
            $session->getFlashBag()->add('success', $message);

            return new RedirectResponse($team->getUrl());
        }, null, array('team' => $team, 'player' => $player));
    }
}
