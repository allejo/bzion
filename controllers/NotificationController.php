<?php

class NotificationController extends HTMLController
{
    public function listAction(Player $me)
    {
        $this->requireLogin();
        $not = $me->notify('text', array(
            'data' => array(
                'text' => 'heyooo'
            )
        ));

        return array();
    }
}
