<?php
/**
 * This file contains an exception class
 *
 * @package    BZiON\Exceptions
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An Exception about a model which doesn't exist in the database
 * @package BZiON\Exceptions
 */
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
