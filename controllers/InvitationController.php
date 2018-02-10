<?php

use BZIon\Event\Events;
use BZIon\Event\TeamInviteEvent;
use BZIon\Event\TeamJoinEvent;
use BZIon\Form\Creator\InvitationFormCreator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class InvitationController extends CRUDController
{
    public function acceptAction(Player $me, Invitation $invitation)
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

        if ($invitation->getTeam()->isDeleted()) {
            $invitation->setExpired();

            throw new ForbiddenException("This invitation is for a team which has been deleted.");
        }

        $inviter = $invitation->getSentBy()->getEscapedUsername();
        $team    = $invitation->getTeam();

        return $this->showConfirmationForm(function () use ($invitation, $team, $me) {
            $team->addMember($me->getId());
            $invitation->setStatus(Invitation::STATUS_ACCEPTED);
            $invitation->setExpired();
            Service::getDispatcher()->dispatch(Events::TEAM_JOIN, new TeamJoinEvent($team, $me));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to accept the invitation from $inviter to join {$team->getEscapedName()}?",
            "You are now a member of {$team->getName()}");
    }

    public function inviteAction(Player $me, Team $team, Player $player)
    {
        if (!$me->canEdit($team)) {
            throw new ForbiddenException("You are not allowed to invite a player to that team!");
        }

        $creator = new InvitationFormCreator($team, $me, $this);
        $form = $creator->create()->handleRequest(self::getRequest());

        if ($form->isSubmitted()) {
            $this->validate($form);

            if ($form->isValid()) {
                $clickedButton = $form->getClickedButton()->getName();

                if ($clickedButton === 'submit') {
                    $invitation = $creator->enter($form);

                    self::getFlashBag()->add('success', sprintf('"%s" has been invited to "%s."', $invitation->getInvitedPlayer()->getName(), $team->getName()));

                    return $this->redirectTo($team);
                }

                return (new RedirectResponse($this->getPreviousURL()));
            }
        } else {
            $form->get('invited_player')->setData($player);
        }

        return [
            'form' => $form->createView(),
            'team' => $team,
            'player' => $player,
        ];
    }
}
