<?php

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InvitationController extends CRUDController
{
    public function showAction(Team $team)
    {
        return array("team" => $team);
    }

    public function listAction()
    {
        return array("teams" => Team::getTeams());
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function deleteAction(Player $me, Team $team)
    {
        return $this->delete($team, $me);
    }

    public function acceptAction(Invitation $invitation, Player $me)
    {
        if (!$me->isTeamless())
            throw new ForbiddenException("You can't join a new team until you leave your current one.");

        if ($invitation->getInvitedPlayer()->getId() != $me->getId())
            throw new ForbiddenException("This invitation isn't for you!");

        if ($invitation->getExpiration()->lt(TimeDate::now()))
            throw new ForbiddenException("This invitation has expired");

        $inviter = $invitation->getSentBy()->getEscapedUsername();
        $team    = $invitation->getTeam();

        return $this->showConfirmationForm(function () use (&$invitation, &$team, &$me) {
            $team->addMember($me->getId());
            $invitation->updateExpiration();

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to accept the invitation from $inviter to join {$team->getEscapedName()}?",
            "You are now a member of {$team->getName()}");
    }
}
