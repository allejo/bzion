<?php


class NotificationController extends HTMLController
{
    public function listAction(Player $me)
    {
        $this->requireLogin();
        var_dump($me->notify('heyooo'));

        return array();
    }
}
