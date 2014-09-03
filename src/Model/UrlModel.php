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
abstract class UrlModel extends PermissionModel
{
    /**
     * Get the name of the route that shows the object
     * @param  string $action The route's suffix
     * @return string
     */
    public static function getRouteName($action='show')
    {
        return self::getParamName() . "_$action";
    }

    /**
     * Get the name of the object's parameter in the route
     * @return string
     */
    public static function getParamName()
    {
        return static::getType();
    }

    /**
     * Gets a human-readable format of the model's type
     * @return string
     */
    public static function getTypeForHumans()
    {
        return static::getParamName();
    }

    /**
     * Get an object's url
     *
     * @param string  $action   The action to perform (`show`, `list` or `delete`)
     * @param boolean $absolute Whether to return an absolute URL
     *
     * @return string A permanent link
     */
    public function getURL($action='show', $absolute=false)
    {
        return static::getPermaLink($action, $absolute);
    }

    /**
     * Get an object's permanent url
     *
     * @param string  $action   The action to perform (`show`, `list` or `delete`)
     * @param boolean $absolute Whether to return an absolute URL
     *
     * @return string A permanent link
     */
    public function getPermaLink($action='show', $absolute=false)
    {
        return Service::getGenerator()->generate(static::getRouteName($action),
                   array(static::getParamName() => $this->getId()), $absolute);
    }
}
