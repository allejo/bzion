<?php

/**
 * The header used in HTML pages
 */
abstract class Header
{
    /**
     * Redirect the page using PHP's header() function
     * @param string $location The page to redirect to
     * @param bool   $override True if $location is an absolute path (e.g `http://google.com/`), false to prepend the base URL of the website to the path
     */
    public static function go($location = "/", $override = false)
    {
        if ($override) {
            header("Location: $location");
        } elseif (strtolower($location) == "home" || $location == "/") {
            header("Location: " . self::getBasePath());
        } else {
            header("Location: " . self::getBasePath() . $location);
        }

        die();
    }

    /**
     * Returns the root path from which this request is executed.
     *
     * The base path never ends with a `/`.
     * @param  boolean $absolute Whether to return an absolute path (e.g: http://example.com/bzion as opposed to /bzion)
     * @return string  The raw path
     */
    public static function getBasePath($absolute=false)
    {
        //$host = $absolute ? Service::getRequest()->getSchemeAndHttpHost() : "";

        //return $host . Service::getRequest()->getBasePath();
        if($absolute)

            return Service::getRequest()->getSchemeAndHttpHost();
        else
            return Service::getRequest()->getBasePath();
    }
}
