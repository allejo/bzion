<?php


class NotificationController extends HTMLController
{
    public function listAction(Player $me)
    {
        $this->requireLogin();
        $me->notify('heyooo');

        return array();
    }
}
