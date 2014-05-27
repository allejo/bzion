<?php
/**
 * This file contains functionality to communicate with the pusher.com push service
 *
 * @package    BZiON\NotificationAdapters
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An interface to the pusher.com service
 * @todo Perform an asynchronous request?
 * @package    BZiON\NotificationAdapters
 */
class PusherAdapter extends NotificationAdapter
{
    /**
     * The Pusher instance
     * @var Pusher
     */
    private $pusher;

    public function __construct()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function trigger($channel, $message)
    {

    }

    /**
     * {@inheritDoc}
     */
    public static function isEnabled()
    {
        if (!parent::isEnabled())
            return false;

        return (bool) ENABLE_PUSHER;
    }
}
