<?php
/**
 * This file contains an exception class
 *
 * @package    BZiON\Exceptions
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An Exception about something that we couldn't locate
 * @package BZiON\Exceptions
 */
class NotFoundException extends HTTPException
{
    public function getStatusCode()
    {
        return 404;
    }
}
