<?php

/**
 * The header used in HTML pages
 */
abstract class Header
{
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
