<?php
/**
 * This file contains an exception class
 *
 * @package    BZiON\Exceptions
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An Exception to signify erroneous input data
 * @package BZiON\Exceptions
 */
class BadRequestException extends HTTPException
{
    protected $message = "Bad request";

    public static function getErrorCode()
    {
        return 200;
    }
}
