<?php

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

    /**
     * Twig Template engine
     * @var EngineInterface
     */
    private static $templateEngine;

    public static function setRequest($request) {
        self::$request = $request;
    }

    public static function getRequest() {
        if (!self::$request) {
            $request = Request::createFromGlobals();
            $request->setSession(self::getNewSession());
            self::setRequest($request);
        }
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

    /**
     * @param SessionInterface|null $session
     */
    public static function setSession($session) {
        self::getRequest()->setSession($session);
    }

    /**
     * @return SessionInterface|null
     */
    public static function getSession() {
        return self::getRequest()->getSession();
    }

    /**
     * Create a new session
     * @return Session
     */
    private static function getNewSession() {
        $newSession = new Session();
        $newSession->start();
        return $newSession;
    }

    public static function getTemplateEngine() {
        return self::$templateEngine;
    }

    public static function setTemplateEngine($templateEngine) {
        self::$templateEngine = $templateEngine;
    }
}
