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
    public static function getRouteName($action = 'show')
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
     * @param string  $action   The action to perform (e.g `show`, `list` or `delete`)
     * @param bool    $absolute Whether to return an absolute URL
     * @param array   $params   Extra parameters to pass to the URL generator
     * @param boolean $vanity   Whether to force the URL to contain just the alias
     *
     * @return string A link
     */
    public function getURL($action = 'show', $absolute = false, $params = array(), $vanity = false)
    {
        return static::getPermaLink($action, $absolute, $params);
    }

    /**
     * Get an object's permanent url
     *
     * @param string  $action   The action to perform (e.g `show`, `list` or `delete`)
     * @param bool $absolute Whether to return an absolute URL
     * @param array   $params   Extra parameters to pass to the URL generator
     *
     * @return string A permanent link
     */
    public function getPermaLink($action = 'show', $absolute = false, $params = array())
    {
        return $this->getLink($this->getId(), $action, $absolute, $params);
    }

    /**
     * Generate a link for a route related to this object
     *
     * @param mixed   $identifier A parameter representing the model (e.g an ID or alias)
     * @param string  $action     The action to perform
     * @param bool $absolute   Whether to return an absolute URL
     * @param array   $params     Extra parameters to pass to the URL generator
     *
     * @return string A link
     */
    protected function getLink($identifier, $action, $absolute, $params)
    {
        return Service::getGenerator()->generate(
            static::getRouteName($action),
            array_merge(array(static::getParamName() => $identifier), $params),
            $absolute
        );
    }
}
