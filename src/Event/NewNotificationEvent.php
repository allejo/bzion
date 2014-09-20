<?php
/**
 * This file contains a notification event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event thrown each time a new notification for a user is created
 */
class NewNotificationEvent extends Event {
    /**
     * @var \Notification
     */
    protected $notification;

    /**
     * Create a new event
     *
     * @param \Notification $notification The new notification
     */
    public function __construct(\Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the new notification
     * @return \Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
