<?php
/**
 * This file contains an exception class
 *
 * @package    BZiON\Exceptions
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * An Exception with an HTTP error code, the message of which is visible to the user
 * @package BZiON\Exceptions
 */
abstract class HTTPException extends Exception implements HttpExceptionInterface
{
    /**
     * The HTTP error code that the response should include
     * @return int
     */
    public function getStatusCode()
    {
        return 500;
    }

    /**
     * Headers to be included in the response
     * @return array
     */
    public function getHeaders()
    {
        return array();
    }
}
