<?php
/**
 * This file contains functionality relating Symfony2 components such as the template engine, requests, and sessions
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Service
{
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
     * @var Twig_Environment
     */
    private static $templateEngine;

    /**
     * Symfony's FormFactory
     * @var FormFactory
     */
    private static $formFactory;

    /**
     * The kernel's environment (prod, debug, profile or test)
     * @var string
     */
    private static $environment;

    /**
     * The model memory cache
     * @var ModelCache
     */
    private static $modelCache;

    /**
     * The AppKernel's container
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @param Request $request
     */
    public static function setRequest($request)
    {
        self::$request = $request;
    }

    public static function getRequest()
    {
        if (!self::$request) {
            $request = Request::createFromGlobals();
            $request->setSession(self::getNewSession());
            self::setRequest($request);
        }

        return self::$request;
    }

    /**
     * Sets the URL Generator.
     * @param  UrlGeneratorInterface $generator
     * @return void
     */
    public static function setGenerator($generator)
    {
        self::$generator = $generator;
    }

    public static function getGenerator()
    {
        return self::$generator;
    }

    /**
     * Gets a parameter
     *
     * @param  string $name The parameter name
     * @return mixed  The parameter value
     */
    public static function getParameter($name)
    {
        return self::getContainer()->getParameter($name);
    }

    /**
     * @param SessionInterface $session
     */
    public static function setSession($session)
    {
        self::getRequest()->setSession($session);
    }

    /**
     * @return SessionInterface
     */
    public static function getSession()
    {
        return self::getRequest()->getSession();
    }

    /**
     * Create a new session
     * @return Session
     */
    public static function getNewSession()
    {
        $newSession = new Session();
        $newSession->start();

        return $newSession;
    }

    public static function getTemplateEngine()
    {
        return self::$templateEngine;
    }

    public static function setTemplateEngine($templateEngine)
    {
        self::$templateEngine = $templateEngine;
    }

    public static function getFormFactory()
    {
        return self::$formFactory;
    }

    public static function setFormFactory($formFactory)
    {
        self::$formFactory = $formFactory;
    }

    public static function getEnvironment()
    {
        return self::$environment;
    }

    public static function setEnvironment($environment)
    {
        self::$environment = $environment;
    }

    public static function getModelCache()
    {
        return self::$modelCache;
    }

    public static function setModelCache($modelCache)
    {
        self::$modelCache = $modelCache;
    }

    public static function getContainer()
    {
        return self::$container;
    }

    public static function setContainer(&$container)
    {
        self::$container = $container;
    }

    public static function getDispatcher()
    {
        return self::$container->get('event_dispatcher');
    }

    public static function isDebug()
    {
        return self::getParameter('kernel.debug');
    }
}
