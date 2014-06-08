<?php
/**
 * This file contains an exception class
 *
 * @package    BZiON\Exceptions
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An Exception which should be used when the player is denied access to a page
 * @package BZiON\Exceptions
 */
class ForbiddenException extends HTTPException
{
    public static function getErrorCode()
    {
        return 200;
    }
}
