<?php

class NotificationController extends HTMLController
{
    public function listAction(Player $me)
    {
        $this->requireLogin();

        $me->notify('text', array(
                'text' => 'heyooo'
            )
        );

        return array('notifications' => Notification::getNotifications($me->getId()));
    }
}
