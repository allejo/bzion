<?php

class NotificationController extends HTMLController
{
    public function listAction(Player $me)
    {
        $this->requireLogin();

        $query = $this->getQueryBuilder()
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
            $date = $notification->getTimestamp()->startOfMonth();

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
