<?php

use Symfony\Component\HttpFoundation\RedirectResponse;

class BanController extends CRUDController
{
    public function showAction(Ban $ban)
    {
        return array("ban" => $ban);
    }

    public function listAction()
    {
        return array("bans" => $this->getQueryBuilder()->sortBy('updated')->reverse()->getModels());
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

    protected function update($form, $ban, $me)
    {
        $ban->setIPs($form->get('ip_addresses')->getData())
            ->setExpiration($this->getExpiration($form))
            ->setReason($form->get('reason')->getData())
            ->setServerMessage($form->get('server_message')->getData())
            ->setAllowServerJoin($form->get('server_join_allowed')->getData());

        return $ban;
    }

    protected function enter($form, $me)
    {
        return Ban::addBan(
            $form->get('player')->getData()->getId(),
            $me->getId(),
            $this->getExpiration($form),
            $form->get('reason')->getData(),
            $form->get('server_message')->getData(),
            $form->get('ip_addresses')->getData(),
            $form->get('server_join_allowed')->getData()
        );
    }

    /**
     * Get the expiration time of the ban based on the fields of the form
     *
     * @param  Form $form The form
     * @return TimeDate|null
     */
    private function getExpiration($form)
    {
        if ($form->get('automatic_expiration')->getData()) {
            return $form->get('expiration')->getData();
        } else {
            return null;
        }
    }
}
