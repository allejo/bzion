<?php
/**
 * This file contains a class that responds to events
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * An event subscriber for bzion events
 */
class EventSubscriber implements EventSubscriberInterface {
    public static function getSubscribedEvents()
    {
        return array(
            'message.new' => 'onNewMessage',
        );
    }

    public function onNewMessage(NewMessageEvent $event)
    {
    }
}
