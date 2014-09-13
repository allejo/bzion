<?php
/**
 * This file contains a unifying class for all the notification adapters for push services
 *
 * @package    BZiON\NotificationAdapters
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\NotificationAdapter;

/**
 * An external push service, used to send notifications to users in real-time
 * while they are using the web interface
 * @package    BZiON\NotificationAdapters
 */
abstract class NotificationAdapter
{
    /**
     * Find whether we should/can send messages using this adapter
     * @return bool
     */
    public static function isEnabled()
    {
        // Don't push notifications when testing
        return (\Service::getEnvironment() != "test");
    }

    /**
     * Trigger a notification
     * @param string $channel The channel to send the message on
     * @param string $message The content of the message to send
     */
    abstract public function trigger($channel, $message);
}
