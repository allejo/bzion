<?php

class NotificationController extends HTMLController
{
    public function listAction(Player $me)
    {
        $this->requireLogin();

        $query = Notification::getQueryBuilder()
            ->active()
            ->where('receiver')->is($me)
            ->sortBy('timestamp')->reverse();

        $notifications = $query->getModels();

        // Mark the notifications as read after fetching them, so we can show
        // to the user which notifications he hadn't seen
        $query->markRead();

        return array('notifications' => $this->chunk($notifications));
    }

    /**
     * Separates notifications based on their timestamp
     *
     * @return array[]
     */
    private function chunk($notifications)
    {
        $result = array();
        $index  = -1;

        foreach ($notifications as $notification) {
            // Only keep the year and the month to separate the notifications
            $date = $notification->getTimestamp();
            $date = TimeDate::create($date->year, $date->month, 1, 0, 0, 0);

            // Create a new element in the $result array for every month
            if ($index == -1 || $result[$index]['date'] != $date) {
                $result[] = array(
                    'notifications' => array(),
                    'date' => $date
                );

                $index++;
            }

            $result[$index]['notifications'][] = $notification;
        }

        return $result;
    }
}
