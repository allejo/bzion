<?php
/**
 * This file contains a model interface
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An interface for group events
 * @package    BZiON\Models
 */
interface GroupEventInterface
{
    /**
     * Find out whether the event is a message and not a generic group event
     * (such as a rename or member join)
     *
     * @return boolean
     */
    public function isMessage();

    /**
     * Get the time when the event occurred
     *
     * @return TimeDate
     */
    public function getTimestamp();
}
