<?php

use BZIon\Form\Creator\BanFormCreator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class BanController extends CRUDController
{
    public function showAction(Ban $ban)
    {
        return array("ban" => $ban);
    }

    public function listAction(Request $request)
    {
        return array("bans" => $this->getQueryBuilder()
            ->sortBy('updated')->reverse()
            ->limit(15)->fromPage($request->query->get('page', 1))
            ->getModels());
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function editAction(Player $me, Ban $ban)
    {
        return $this->edit($ban, $me, "ban");
    }

    public function deleteAction(Player $me, Ban $ban)
    {
        return $this->delete($ban, $me);
    }

    public function unbanAction(Player $me, Ban $ban)
    {
        if (!$this->canEdit($me, $ban)) {
            throw new ForbiddenException("You are not allowed to unban a player.");
        } elseif ($ban->hasExpired()) {
            throw new ForbiddenException("Sorry, this ban has already expired.");
        }

        $victim = $ban->getVictim()->getEscapedUsername();

        return $this->showConfirmationForm(function () use (&$ban) {
            $ban->expire();

            return new RedirectResponse($ban->getUrl());
        }, "Are you sure you want to unban <strong>$victim</strong>?",
            "$victim's ban has been deactivated successfully", "Unban");
    }
}
