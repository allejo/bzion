<?php
/**
 * This file contains functionality to communicate with the pusher.com push service
 *
 * @package    BZiON\NotificationAdapters
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\NotificationAdapter;

use BZIon\Debug\Debug;

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
        $this->pusher = new Pusher(
            \Service::getParameter('bzion.features.pusher.key'),
            \Service::getParameter('bzion.features.pusher.secret'),
            \Service::getParameter('bzion.features.pusher.app_id')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function trigger($channel, $message)
    {
        Debug::startStopwatch("notification.trigger.pusher");

        $this->pusher->trigger($channel, 'unnamed-event', array ( 'message' => $message));

        Debug::finishStopwatch("notification.trigger.pusher");
    }

    /**
     * {@inheritDoc}
     */
    public static function isEnabled()
    {
        if (!parent::isEnabled())
            return false;

        return \Service::getParameter('bzion.features.pusher.enabled');
    }
}
