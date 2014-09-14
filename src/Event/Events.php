<?php
/**
 * This file contains a list of events that may or may not happen during a request
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event list
 */
class Events {
    /**
     * The message event is thrown each time a new message is sent or a
     * conversation is created
     *
     * The event listener receives a BZIon\Event\NewMessageEvent instance
     *
     * @var string
     */
    const MESSAGE_NEW = 'message.new';
}
