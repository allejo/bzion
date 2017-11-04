<?php

class NotificationController extends HTMLController
{
    public function listAction(Player $me)
    {
        $this->requireLogin();

        $query = Notification::getQueryBuilder()
            ->where('receiver')->is($me)
            ->limit(15)->fromPage($this->getCurrentPage())
            ->sortBy('timestamp')->reverse()
        ;

        $notifications = $query->getModels($fast = true);
        $notificationsGrouped = __::groupBy($notifications, function ($item) {
            /** @var Notification $item */
            return $item->getTimestamp()->format('F Y');
        });

        // Mark the notifications as read after fetching them, so we can show
        // to the user which notifications he hadn't seen
        $query->markRead();

        return [
            'notifications' => $notificationsGrouped
        ];
    }
}
