<?php
/**
 * This file contains the skeleton for all of the database objects
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use \Michelf\Markdown;

/**
 * A database object (e.g. A player or a team)
 * @package    BZiON\Models
 */
abstract class Model extends CachedModel
{
    /**
     * Generates a string with the object's type and ID
     */
    public function __toString()
    {
        return get_class($this) . " #" . $this->getId();
    }

    /**
     * Find if the model is in the trash can (or doesn't exist)
     *
     * @return boolean True if the model has been deleted
     */
    public function isDeleted()
    {
        if (!$this->isValid())
            return true;

        if (!isset($this->status))
            return false;

        return ($this->status == "deleted");
    }

    /**
     * Converts an array of IDs to an array of Models
     * @param  int[] $idArray The list of IDs
     * @return array An array of models
     */
    public static function arrayIdToModel($idArray)
    {
        $return = array();
        foreach ($idArray as $id) {
            $return[] = new static($id);
        }

        return $return;
    }

    /**
     * Convert a markdown string to HTML. This function is used to have one global configuration with all markdown parsing
     *
     * @param string $text The markdown to be parsed to HTML
     *
     * @return string Return the parsed markdown
     */
    public static function mdTransform($text)
    {
        $mdParser = new Markdown();
        $mdParser->no_entities = true;

        return $mdParser->transform($text);
    }

    /**
     * Escape special HTML characters from a string
     * @param  string  $string
     * @return $string
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
