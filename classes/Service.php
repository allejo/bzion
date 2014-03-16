<?php

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class Service {
    /**
     * Symfony's URL Generator
     * @var UrlGeneratorInterface;
     */
    private static $generator;

     /**
      * Symfony's Request class
      * @var Request
      */
    private static $request;

    public static function setRequest($request) {
        self::$request = $request;
    }

    public static function getRequest() {
        if (!self::$request)
            self::setRequest(Request::createFromGlobals());
        return self::$request;
    }

    /**
     * Sets the URL Generator.
     * @param UrlGeneratorInterface $generator
     * @return void
     */
    public static function setGenerator($generator) {
        self::$generator = $generator;
    }

    public static function getGenerator() {
        return self::$generator;
    }
}
