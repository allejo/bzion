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
     * The exception's message
     * @var null|string
     */
    protected $message = null;

    /**
     * Construct new Exception
     * @param null|string $message  The Exception's message
     *                              (null for the default one)
     * @param int         $code     The Exception's code
     * @param Exception   $previous The previous exception
     */
    public function __construct(
        $message = null,
        $code = 0,
        Exception $previous = null
    ) {
        if ($message === null) {
            // Workaround for HHVM that doesn't support shadowing of properties
            // for Exceptions
            $message = $this->message;
        }

        return parent::__construct($message, $code, $previous);
    }

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
