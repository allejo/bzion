<?php
/**
 * This file contains functionality to communicate with bzion's PHP websocket
 *
 * @package    BZiON\NotificationAdapters
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\NotificationAdapter;

use BZIon\Debug\Debug;

/**
 * An interface to our websocket
 * @package    BZiON\NotificationAdapters
 */
class WebSocketAdapter extends NotificationAdapter
{
    /**
     * {@inheritDoc}
     *
     * @todo Error handling
     */
    public function trigger($channel, $message)
    {
        Debug::startStopwatch("notification.trigger.websocket");

        $port = \Service::getParameter('bzion.features.websocket.pull_port');

        $fp = @stream_socket_client("tcp://127.0.0.1:" . $port, $errno, $errstr, 1, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);

        @fwrite($fp, json_encode(array(
            'event' => array(
                'type' => $channel,
                'data' => $message,
            )
        )) . "\n");

        // Don't fclose() the connection because of a weird bug with React

        Debug::finishStopwatch("notification.trigger.websocket");
    }

    /**
     * {@inheritDoc}
     */
    public static function isEnabled()
    {
        if (!parent::isEnabled()) {
            return false;
        }

        return \Service::getParameter('bzion.features.websocket.enabled');
    }
}
