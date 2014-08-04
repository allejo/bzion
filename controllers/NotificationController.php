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

        $notifications = Notification::getQueryBuilder()
            ->active()
            ->where('receiver')->is($me)
            ->markRead()
            ->getModels();

        return array('notifications' => $notifications);
    }
}
