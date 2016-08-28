<?php

use Symfony\Component\HttpFoundation\Request;

class NotificationController extends HTMLController
{
    public function listAction(Player $me, Request $request)
    {
        $this->requireLogin();

        $query = $this->getQueryBuilder()
            ->where('receiver')->is($me)
            ->limit(15)->fromPage($request->query->get('page', 1))
            ->sortBy('timestamp')->reverse();

        $notifications = $query->getModels($fast = true);

        // Mark the notifications as read after fetching them, so we can show
        // to the user which notifications he hadn't seen
        $query->markRead();

        return array('notifications' => $this->chunk($notifications));
    }

    /**
     * Separates notifications based on their timestamp
     *
     * @param  Notification[] $notifications The list of notifications
     * @return array[]
     */
    private function chunk($notifications)
    {
        $result = array();
        $index  = -1;

        foreach ($notifications as $notification) {
            $timezone = $this->getMe()->getTimezone();
            $date = $notification->getTimestamp()
                ->copy()
                ->timezone($timezone)
                ->startOfMonth();

            // Create a new element in the $result array for every month
            if ($index == -1 || $result[$index]['date'] != $date) {
                $result[] = array(
                    'notifications' => array(),
                    'date'          => $date
                );

                ++$index;
            }

            $result[$index]['notifications'][] = $notification;
        }

        return $result;
    }
}
