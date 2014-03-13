<?php

require_once("bzion-load.php");

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$routes = new RouteCollection();
$routes->add('index', new Route('/', array('controller' => 'index')));
$routes->add('bans', new Route('/bans', array('controller' => 'bans')));
$routes->add('login', new Route('/login', array('controller' => 'login')));
$routes->add('logout', new Route('/logout', array('controller' => 'logout')));
$routes->add('matches', new Route('/matches', array('controller' => 'matches')));

$routes->add('messages', new Route('/messages', array('controller' => 'messages')));
$routes->add('view_discussion', new Route('/messages/{id}', array('controller' => 'messages')));

$routes->add('news', new Route('/news', array('controller' => 'news')));

$routes->add('players', new Route('/players', array('controller' => 'players')));
$routes->add('view_player', new Route('/players/{slug}', array('controller' => 'players')));

$routes->add('profile', new Route('/profile', array('controller' => 'profile')));
$routes->add('servers', new Route('/servers', array('controller' => 'servers')));

$routes->add('teams', new Route('/teams', array('controller' => 'teams')));
$routes->add('view_team', new Route('/teams/{slug}', array('controller' => 'teams')));

$routes->add('custom_page', new Route('/{slug}', array('controller' => 'pages')));


$context = new RequestContext();
$context->fromRequest(Request::createFromGlobals());

$matcher = new UrlMatcher($routes, $context);

$parameters = $matcher->matchRequest(Request::createFromGlobals());


require ("controllers/" . $parameters['controller'] . ".php");
