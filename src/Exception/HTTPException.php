<?php
/**
 * This file contains an exception class
 *
 * @package    BZiON\Exceptions
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An Exception with an HTTP error code, the message of which is visible to the user
 * @package BZiON\Exceptions
 */
abstract class HTTPException extends Exception
{
    /**
     * The HTTP error code that the response should include
     * @return int
     */
    public static function getErrorCode()
    {
        return 500;
    }
}
