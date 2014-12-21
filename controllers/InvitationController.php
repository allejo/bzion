<?php

use BZIon\Event\Events;
use BZIon\Event\TeamInviteEvent;
use BZIon\Event\TeamJoinEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InvitationController extends CRUDController
{
    public function acceptAction(Invitation $invitation, Player $me)
    {
        if (!$me->isTeamless()) {
            throw new ForbiddenException("You can't join a new team until you leave your current one.");
        }

        if ($invitation->getInvitedPlayer()->getId() != $me->getId()) {
            throw new ForbiddenException("This invitation isn't for you!");
        }

        if ($invitation->getExpiration()->lt(TimeDate::now())) {
            throw new ForbiddenException("This invitation has expired");
        }

        $inviter = $invitation->getSentBy()->getEscapedUsername();
        $team    = $invitation->getTeam();

        return $this->showConfirmationForm(function () use ($invitation, $team, $me) {
            $team->addMember($me->getId());
            $invitation->updateExpiration();
            Service::getDispatcher()->dispatch(Events::TEAM_JOIN, new TeamJoinEvent($team, $me));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to accept the invitation from $inviter to join {$team->getEscapedName()}?",
            "You are now a member of {$team->getName()}");
    }

    public function inviteAction(Team $team, Player $player, Player $me)
    {
        if (!$me->canEdit($team)) {
            throw new ForbiddenException("You are not allowed to invite a player to that team!");
        } elseif ($team->isMember($player->getId())) {
            throw new ForbiddenException("The specified player is already a member of that team.");
        } elseif (Invitation::hasOpenInvitation($player->getId(), $team->getId())) {
            throw new ForbiddenException("This player has already been invited to join the team.");
        }

        return $this->showConfirmationForm(function () use ($team, $player, $me) {
            $invite = Invitation::sendInvite($player->getId(), $me->getId(), $team->getId());
            Service::getDispatcher()->dispatch(Events::TEAM_INVITE, new TeamInviteEvent($invite));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to invite {$player->getEscapedUsername()} to {$team->getEscapedName()}?",
            "Player {$player->getUsername()} has been invited to {$team->getName()}");
    }
}
