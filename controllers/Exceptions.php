<?php
/**
 * This file contains Exception classes that should be used inside Controllers
 *
 * @package    BZiON\Controllers
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An Exception with an HTTP error code
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

class ModelNotFoundException extends NotFoundException
{
    /**
     * The type of the model
     * @var string
     */
    private $type = '';

    public function __construct($type, $code = 0, Exception $previous = null)
    {
        $this->type = strtolower($type);

        $message = "The specified $this->type could not be found";
        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets the type of the model that we couldn't find
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}

class NotFoundException extends HTTPException
{
    public static function getErrorCode()
    {
        return 404;
    }
}

class ForbiddenException extends HTTPException
{
    public static function getErrorCode()
    {
        return 403;
    }
}

class BadRequestException extend HTTPException
{
    public function __construct($message="Bad request", $code = 0, Exception $previous = null)
    {
        parent::_construct($message, $code, $previous);
    }

    public static function getErrorCode()
    {
        return 400;
    }
}
