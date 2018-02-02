<?php
/**
 * This file contains functionality relating Symfony2 components such as the template engine, requests, and sessions
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Cache\ModelCache;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Service
 */
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
     * @var AppKernel
     */
    private static $kernel;

    private static $qbConnection;
    private static $qbConfig;

    public static function setQueryBuilderConfig(array $config)
    {
        self::$qbConfig = $config;
    }

    public static function getQueryBuilderConnection()
    {
        if (!self::$qbConnection) {
            self::$qbConnection = new Connection('mysql', self::$qbConfig);
        }

        return self::$qbConnection;
    }

    /**
     * @param Request $request
     */
    public static function setRequest($request)
    {
        self::$request = $request;
    }

    /**
     * @return Request
     */
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

    /**
     * @return UrlGeneratorInterface
     */
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

    /**
     * @return FormFactory
     */
    public static function getFormFactory()
    {
        return self::$formFactory;
    }

    /**
     * @param FormFactory $formFactory
     */
    public static function setFormFactory($formFactory)
    {
        self::$formFactory = $formFactory;
    }

    /**
     * @return string
     */
    public static function getEnvironment()
    {
        return self::$environment;
    }

    /**
     * @param string $environment
     */
    public static function setEnvironment($environment)
    {
        self::$environment = $environment;
    }

    /**
     * @return ModelCache
     */
    public static function getModelCache()
    {
        return self::$modelCache;
    }

    /**
     * @param ModelCache $modelCache
     */
    public static function setModelCache($modelCache)
    {
        self::$modelCache = $modelCache;
    }

    /**
     * @return string[][]
     */
    public static function getSiteThemes()
    {
        return self::getParameter('bzion.site.themes');
    }

    /**
     * @return string
     */
    public static function getDefaultSiteTheme()
    {
        $themes = self::getSiteThemes();

        return $themes[0]['slug'];
    }

    /**
     * @return ContainerInterface
     */
    public static function getContainer()
    {
        return self::$kernel->getContainer();
    }

    /**
     * @param $kernel
     */
    public static function setKernel($kernel)
    {
        self::$kernel = $kernel;
    }

    /**
     * @return EventDispatcher
     */
    public static function getDispatcher()
    {
        return self::getContainer()->get('event_dispatcher');
    }

    /**
     * @return bool
     */
    public static function isDebug()
    {
        return self::getParameter('kernel.debug');
    }
}
