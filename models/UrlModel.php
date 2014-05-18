<?php
/**
 * This file contains functionality linking database objects' aliases with Symfony2's URL routing component
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A Model that has a URL
 * @package    BZiON\Models
 */
abstract class UrlModel extends Model
{
    /**
     * Get the name of the route that shows the object
     * @return string
     */
    protected static function getRouteName()
    {
        return self::toSnakeCase(get_called_class()) . "_show";
    }

    /**
     * Get the name of the object's parameter in the route
     * @return string
     */
    public static function getParamName()
    {
        return self::toSnakeCase(get_called_class());
    }

    /**
     * Takes a CamelCase string and converts it to a snake_case one
     * @param $input The string to convert
     * @return string
     */
    private static function toSnakeCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * Get an object's url
     *
     * @param boolean $absolute Whether to return an absolute URL
     *
     * @return string A permanent link
     */
    public function getURL($absolute=false)
    {
        return static::getPermaLink($absolute);
    }

    /**
     * Get an object's permanent url
     *
     * @param boolean $absolute Whether to return an absolute URL
     *
     * @return string A permanent link
     */
    public function getPermaLink($absolute=false)
    {
        return Service::getGenerator()->generate(static::getRouteName(), array(static::getParamName() => $this->getId()), $absolute);
    }
}
