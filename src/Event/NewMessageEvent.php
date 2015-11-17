<?php
/**
 * This file contains a messaging event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event thrown each time a new message is sent or a conversation is created
 */
class NewMessageEvent extends Event
{
    /**
     * @var \Message
     */
    protected $message;

    /**
     * @var boolean
     */
    protected $first;

    /**
     * Create a new event
     *
     * @param \Message $message The new message
     * @param boolean  $first   Whether the message is the first in its discussion
     */
    public function __construct(\Message $message, $first)
    {
        $this->message = $message;
        $this->first = $first;
    }

    /**
     * Get the new message
     * @return \Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Find out if the message is the first in its Conversation
     * @return boolean
     */
    public function isFirst()
    {
        return $this->first;
    }
}
