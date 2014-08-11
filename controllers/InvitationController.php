<?php

use Symfony\Component\HttpFoundation\RedirectResponse;

class InvitationController extends CRUDController
{
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
            $team->getLeader()->notify(Notification::TEAM_JOIN, array(
                'player' => $me->getId(),
                'team'   => $team->getId()
            ));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to accept the invitation from $inviter to join {$team->getEscapedName()}?",
            "You are now a member of {$team->getName()}");
    }
}
