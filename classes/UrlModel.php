<?php

/**
 * A Model that has a URL
 */
abstract class UrlModel extends Model {

    /**
        * Get the name of the route that shows the object
        * @return string
        */
    static protected function getRouteName() {
        return strtolower(get_called_class()) . "_show";
    }

    /**
        * Get the name of the object's parameter in the route
        * @return string
        */
    static protected function getParamName() {
        return strtolower(get_called_class());
    }

    /**
     * Get an object's url
     * @param boolean $absolute Whether to return an absolute URL
     */
    public function getURL($absolute=false) {
        return static::getPermaLink($absolute);
    }

    /**
     * Get an object's permanent url
     * @param boolean $absolute Whether to return an absolute URL
     */
    public function getPermaLink($absolute=false) {
        return Service::getGenerator()->generate(static::getRouteName(), array(static::getParamName() => $this->getId()), $absolute);
    }
}
