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

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:" . WEBSOCKET_PULL_PORT);

        var_dump('triggered');

        $socket->send(json_encode(array(
            'event' => array(
                'type' => 'global_notification',
                'message' => $message,
            )
        )));

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
