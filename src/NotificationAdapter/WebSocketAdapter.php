<?php
/**
 * This file contains functionality to communicate with bzion's PHP websocket
 *
 * @package    BZiON\NotificationAdapters
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An interface to our websocket
 * @package    BZiON\NotificationAdapters
 */
class WebSocketAdapter extends NotificationAdapter
{
    /**
     * {@inheritDoc}
     */
    public function trigger($channel, $message)
    {
        Debug::startStopwatch("notification.trigger.websocket");

        $fp = stream_socket_client("tcp://127.0.0.1:". WEBSOCKET_PULL_PORT, $errno, $errstr, 1);

        var_dump('triggered');

        fwrite($fp, json_encode(array(
            'event' => array(
                'type' => 'global_notification',
                'message' => $message,
            )
        )));

        fclose($fp);

        Debug::finishStopwatch("notification.trigger.websocket");
    }

    /**
     * {@inheritDoc}
     */
    public static function isEnabled()
    {
        if (!parent::isEnabled())
            return false;

        return (bool) ENABLE_WEBSOCKET;
    }
}
