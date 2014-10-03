<?php
/**
 * This file contains an exception class
 *
 * @package    BZiON\Exceptions
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An Exception thrown when a user tries to access a development environment
 * and the `bzion.miscellaneous.development` setting is set to `false`
 *
 * @package BZiON\Exceptions
 */
class ForbiddenDeveloperAccessException extends Exception
{
}
