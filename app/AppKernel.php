<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Bridge\Twig\Extension\RoutingExtension;

class AppKernel extends Kernel {
    private $router;
    private $twig;

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load(__DIR__.'/config.yml');
    }

    public function registerBundles() {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
        );
    }

    public function __construct($environment, $debug) {
        parent::__construct($environment, $debug);

        $locator = new FileLocator(array(__DIR__));

        // Disable caching while on the DEVELOPMENT environment
        $cacheDir = DEVELOPMENT ? null : __DIR__.'/../cache';

        $this->router = new Router(
            new YamlFileLoader($locator),
            'routes.yml',
            array('cache_dir' => $cacheDir)
        );

        Service::setGenerator($this->router->getGenerator());

        // Set up the twig templating environment to parse views
        $loader = new Twig_Loader_Filesystem(__DIR__.'/../views');
        $twig = new Twig_Environment($loader, array(
            'cache' => $cacheDir,
            'debug' => $debug
        ));

        // Load the routing extension to twig, which adds functions such as path()
        $twig->addExtension(new RoutingExtension($this->router->getGenerator()));
        if ($debug)
            $twig->addExtension(new Twig_Extension_Debug());

        $twig->addGlobal("pages", Page::getPages());

        Service::setTemplateEngine($twig);
    }

    public function handle(Request $request, $type=1, $catch=true) {
        $request->setSession(Service::getNewSession());

        $requestContext = new RequestContext();
        $requestContext->fromRequest($request);

        $this->router->setContext($requestContext);

        $parameters = $this->router->matchRequest($request);

        Service::setRequest($request);
        Service::getTemplateEngine()->addGlobal("request", $request);
        Service::getTemplateEngine()->addGlobal("session", $request->getSession());
        Service::getTemplateEngine()->addGlobal("me",
                 new Player($request->getSession()->get('playerId')));

        $con = Controller::getController($parameters);
        $response = $con->callAction();

        $response->sendHeaders();
        $response->sendContent();
    }
}
