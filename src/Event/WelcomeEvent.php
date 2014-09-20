<?php
/**
 * This file contains a text event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event dispatched whenever a new user is created
 */
class WelcomeEvent extends Event {
    /**
     * @var string
     */
    protected $message;

    /**
     * @var \Player
     */
    protected $player;

    /**
     * Create a new event
     *
     * @param string $message The welcome message
     * @param \Player $player The new player
     */
    public function __construct($message, \Player $player)
    {
        $this->message = $message;
        $this->player  = $player;
    }

    /**
     * Get the welcome message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the new player
     *
     * @return \Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * {@inheritDoc}
     */
    public function notify($type)
    {
        return $this->doNotify($this->player, $type);
    }
}
