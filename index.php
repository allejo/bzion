<?php

require_once("bzion-load.php");

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Bridge\Twig\Extension\RoutingExtension;

use Symfony\Component\HttpFoundation\Session\Session;

$locator = new FileLocator(array(__DIR__));

$request = Service::getRequest();
$requestContext = new RequestContext();
$requestContext->fromRequest($request);

// Disable caching while on the DEVELOPMENT environment
$cacheDir = DEVELOPMENT ? null : __DIR__.'/cache';

$router = new Router(
    new YamlFileLoader($locator),
    'routes.yml',
    array('cache_dir' => $cacheDir),
    $requestContext
);

Service::setGenerator($router->getGenerator());

$parameters = $router->matchRequest($request);

// Set up the twig templating environment to parse views
$loader = new Twig_Loader_Filesystem(__DIR__.'/views');
$twig = new Twig_Environment($loader, array(
    'cache' => $cacheDir,
    'debug' => DEVELOPMENT
));

// Load the routing extension to twig, which adds functions such as path()
$twig->addExtension(new RoutingExtension($router->getGenerator()));
if (DEVELOPMENT)
    $twig->addExtension(new Twig_Extension_Debug());

$twig->addGlobal("session", Service::getSession());
$twig->addGlobal("request", $request);
$twig->addGlobal("pages", Page::getPages());
$templating = new TwigEngine($twig, new TemplateNameParser());

Service::setTemplateEngine($templating);

$con = Controller::getController($parameters);
$con->callAction();
