<?php

namespace BZIon\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A twig global that provides information about the app
 */
class AppGlobal
{
    /**
     * The controller handling the request
     * @var \Controller
     */
    private $controller;

    /**
     * Symfony's container
     * @var ContainerInterface
     */
    private $container;

    /**
     * Create new AppGlobal
     *
     * @param Controller         $controller The controller handling the request
     * @param ContainerInterface $container  Symfony's service container
     */
    public function __construct(\Controller $controller, ContainerInterface $container)
    {
        $this->controller = $controller;
        $this->container  = $container;
    }

    /**
     * Get the controller handling the request
     *
     * @return \Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the environment of the kernel
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->container->getParameter('kernel.environment');
    }

    /**
     * Find out whether the kernel has enabled debugging
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->container->getParameter('kernel.debug');
    }

    /**
     * Find out whether maintenance mode is enabled for users of the website
     *
     * @return bool
     */
    public function isMaintenance()
    {
        return $this->container->getParameter('bzion.miscellaneous.maintenance');
    }

    /**
     * Get the name of the website
     *
     * @return string
     */
    public function getSiteTitle()
    {
        return $this->container->getParameter('bzion.site.name');
    }

    /**
     * Get the name of the website
     *
     * @return string
     */
    public function getSiteWelcome()
    {
        return $this->container->getParameter('bzion.site.welcome');
    }

    /**
     * Get the name of the website
     *
     * @return string
     */
    public function getSiteSlug()
    {
        return $this->container->getParameter('bzion.site.slug');
    }

    /**
     * Whether or not the website wide alert is enabled
     *
     * @return bool
     */
    public function isAlertEnabled()
    {
        return $this->container->getParameter('bzion.site.alert.enabled');
    }

    /**
     * The title of the alert
     *
     * @return string
     */
    public function getAlertHeader()
    {
        return $this->container->getParameter('bzion.site.alert.header');
    }

    /**
     * The message of the alert
     *
     * @return string
     */
    public function getAlertMessage()
    {
        return$this->container->getParameter('bzion.site.alert.message');
    }

    /**
     * Get information about sockets
     *
     * @return array
     */
    public function getSocket()
    {
        return array(
            'websocket' => array(
                'enabled' => $this->container->getParameter('bzion.features.websocket.enabled'),
                'port'    => $this->container->getParameter('bzion.features.websocket.push_port')
            )
        );
    }

    /**
     * Get a list of visible pages
     */
    public function getPages()
    {
        return \Page::getPages();
    }
}
