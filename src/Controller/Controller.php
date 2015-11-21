<?php

/**
 * This file contains the skeleton for all of the controllers
 *
 * @package    BZiON\Controllers
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Debug\Debug;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Controller class represents a bunch of pages relating to the same
 * subject (Model in most cases) - for example, there is a PlayerController,
 * a TeamController and a HomeController.
 *
 * Controllers contain special methods called 'actions', which are essentially
 * different pages performing different actions - for example, the
 * TeamController might contain a 'show' action, which renders the team's page,
 * and a 'new' action, which renders the page that is shown to the user when
 * they want to create a new team.
 *
 * Actions have some unique characteristics. Take a look at this sample action:
 *
 * <pre><code>public function showAction(Request $request, Team $team) {
 *   return array('team' => $team);
 * }
 * </code></pre>
 *
 * The following route will make sure that `showAction()` handles the request:
 *
 * <pre><code>team_show:
 *   pattern:    /teams/{team}
 *   defaults: { _controller: 'Team', _action: 'show' }
 * </code></pre>
 *
 * First of all, the method's name should end with `Action`. The parameters
 * are passed dynamically, and the order is insignificant.
 *
 * You can request Symfony's Request or Session class, or even a model, which
 * will be generated based on the route parameters. For example, the route
 * pattern `/posts/{post}/comments/{commentId}` (note how you can use both
 * `comment` and `commentId` as parameters - just make sure to use the correct
 * variable name on the method later) and can be used with actions like these:
 *
 * <code>
 * public function sampleAction
 *   (Request $request, NewsArticle $post, Comment $comment)
 * </code>
 *
 * <code>
 * public function sampleAction
 *   (NewsArticle $post, Session $session, Request $request, Comment $comment)
 * </code>
 *
 * A method's return value can be:
 * - Symfony's Response Class
 * - A string representing the text you want the user to see
 * - An array representing the variables you want to pass to the controller's
 *   view, so that it can be rendered
 *
 * @package BZiON\Controllers
 */
abstract class Controller extends ContainerAware
{
    /**
     * Parameters specified by the route
     * @var ParameterBag
     */
    protected $parameters;

    /**
     * The first controller that was invoked
     * @var Controller
     */
    protected $parent;

    /*
     * An array of data to pass between different parts of the application
     *
     * @var ParameterBag
     */
    public $data;

    /**
     * @param ParameterBag    $parameters The array returned by $request->attributes
     * @param Controller|null $parent     The controller who invoked this controller
     */
    public function __construct($parameters, Controller $parent = null)
    {
        $this->parameters = $parameters;
        $this->parent = $parent ?: $this;
        $this->data = new ParameterBag();

        $this->setContainer(Service::getContainer());
    }

    /**
     * Returns the controller that is assigned to a route
     *
     * @param  ParameterBag $parameters The array returned by $request->attributes
     * @return Controller   The controller
     */
    public static function getController($parameters)
    {
        $ref = new ReflectionClass($parameters->get('_controller') . 'Controller');
        $controller = $ref->newInstance($parameters);

        return $controller;
    }

    /**
     * Call the controller's action specified by the $parameters array
     *
     * @param  string|null $action The action name to call (e.g. `show`), null to invoke the default one
     * @return Response    The action's response
     */
    public function callAction($action = null)
    {
        $this->setup();
        $response = $this->forward($action);
        $this->cleanup();

        return $response->prepare($this->getRequest());
    }

    /**
     * Get the controller's default action name
     *
     * @return string The action's name without the `Action` suffix
     */
    protected function getDefaultActionName()
    {
        return $this->parameters->get('_action') ?: 'default';
    }

    /**
     * Get a controller's action
     *
     * @param  string|null       $action The action name to call (e.g. `show`), null to invoke the default one
     * @return \ReflectionMethod The action method
     */
    public function getAction($action = null)
    {
        if (!$action) {
            $action = $this->getDefaultActionName();
        }

        return new ReflectionMethod($this, $action . 'Action');
    }

    /**
     * Forward the request to another action
     *
     * Please note that this doesn't generate an HTTP redirect, but an
     * internal one - the user sees the original URL, but a different page
     *
     * @param  string $action The action to forward the request to
     * @param  array  $params An additional associative array of parameters to
     *                        provide to the action
     * @param  string|null $controllerName The name of the controller of the
     *                                     action, without the 'Controller'
     *                                     suffix (defaults to the current
     *                                     controller)
     * @return Response
     */
    protected function forward($action, $params = array(), $controllerName = null)
    {
        if (!$action) {
            $action = $this->getDefaultActionName();
        }

        $args = clone $this->parameters;
        $args->add($params);

        if ($controllerName === null) {
            $controller = $this;
        } else {
            $ref = new ReflectionClass($controllerName . 'Controller');
            $controller = $ref->newInstance($args, $this->parent);
        }

        $ret = $controller->callMethod($controller->getAction($action), $args);

        return $controller->handleReturnValue($ret, $action);
    }

    /**
     * Method that will be called before any action
     *
     * @return void
     */
    public function setup()
    {
    }

    /**
     * Method that will be called after all actions
     *
     * @return void
     */
    public function cleanup()
    {
    }

    /**
     * Call one of the controller's methods
     *
     * The arguments are passed dynamically to the method, based on its
     * definition - check the description of the Controller class for more
     * information
     *
     * @param  \ReflectionMethod $method     The method
     * @param  ParameterBag      $parameters The parameter bag representing the route's parameters
     * @return mixed             The return value of the called method
     */
    protected function callMethod($method, $parameters)
    {
        $params = array();

        foreach ($method->getParameters() as $p) {
            if ($model = $this->getObjectFromParameters($p, $parameters)) {
                $params[] = $model;
            } elseif ($parameters->has($p->name)) {
                $params[] = $parameters->get($p->name);
            } elseif ($p->isOptional()) {
                $params[] = $p->getDefaultValue();
            } else {
                throw new MissingArgumentException("Missing parameter $p->name");
            }
        }

        return $method->invokeArgs($this, $params);
    }

    /**
     * Find what to pass as an argument on an action
     *
     * @param ReflectionParameter $modelParameter  The model's parameter we want to investigate
     * @param ParameterBag        $routeParameters The route's parameters
     */
    protected function getObjectFromParameters($modelParameter, $routeParameters)
    {
        $refClass = $modelParameter->getClass();
        $paramName  = $modelParameter->getName();

        if ($refClass !== null && $refClass->isSubclassOf("Model")) {
            // Look for the object's ID/slugs in the routeParameters array
            $model = $this->findModelInParameters($modelParameter, $routeParameters);

            if ($model !== null) {
                return $model;
            }
        }

        // $me -> currently logged in user
        if ($paramName == "me") {
            return self::getMe();
        }

        if ($refClass === null) {
            // No class provived by the method's definition, we don't know
            // what we should pass
            return null;
        }

        switch ($refClass->getName()) {
            case "Symfony\Component\HttpFoundation\Request":
                return $this->getRequest();
            case "Symfony\Component\HttpFoundation\Session\Session":
                return $this->getRequest()->getSession();
            case "Symfony\Component\HttpFoundation\Session\Flash\FlashBag":
                return $this->getRequest()->getSession()->getFlashBag();
            case "Monolog\Logger":
                return $this->getLogger();
            case "Symfony\Component\Form\FormFactory":
                return Service::getFormFactory();
        }

        return null;
    }

    /**
     * Try locating a method's parameter in an array
     *
     * @param  ReflectionParameter $modelParameter  The model's parameter we want to investigate
     * @param  ParameterBag        $routeParameters The route's parameters
     * @return Model|null          A Model or null if it couldn't be found
     */
    protected function findModelInParameters($modelParameter, $routeParameters)
    {
        $refClass = $modelParameter->getClass();
        $paramName  = $modelParameter->getName();

        if ($routeParameters->has($paramName)) {
            $parameter = $routeParameters->get($paramName);
            if (is_object($parameter) && $refClass->getName() === get_class($parameter)) {
                // The model has already been instantiated - we don't need to do anything
                return $parameter;
            }

            return $refClass->getMethod("fetchFromSlug")->invoke(null, $parameter);
        }

        if ($routeParameters->has($paramName . 'Id')) {
            return $refClass->getMethod('get')
                            ->invoke(null, $routeParameters->get($paramName . 'Id'));
        }
    }

    /**
     * Render the action's template
     * @param  array  $params The variables to pass to the template
     * @param  string $action The controller's action
     * @return string The content
     */
    protected function renderDefault($params, $action)
    {
        $templatePath = $this->getName() . "/$action.html.twig";

        return $this->render($templatePath, $params);
    }

    /**
     * Get a Response from the return value of an action
     * @param  mixed    $return Whatever the method returned
     * @param  string   $action The name of the action
     * @return Response The response that the controller wants us to send to the client
     */
    protected function handleReturnValue($return, $action)
    {
        if ($return instanceof Response) {
            return $return;
        }

        $content = null;
        if (is_array($return)) {
            // The controller is probably expecting us to show a view to the
            // user, using the array provided to set variables for the template
            $content = $this->renderDefault($return, $action);
        } elseif (is_string($return)) {
            $content = $return;
        }

        return new Response($content);
    }

    /**
     * Returns the name of the controller without the "Controller" part
     * @return string
     */
    public static function getName()
    {
        return preg_replace('/Controller$/', '', get_called_class());
    }

    /**
     * Returns a configured QueryBuilder for the corresponding model
     *
     * The returned QueryBuilder will only show models visible to the currently
     * logged in user
     *
     * @param  string|null The model whose query builder we should get (null
     *                     to get the builder of the controller's model)
     * @param string $type
     * @return QueryBuilder
     */
    public static function getQueryBuilder($type = null)
    {
        $type = ($type) ?: static::getName();

        return $type::getQueryBuilder()
            ->visibleTo(static::getMe(), static::getRequest()->get('showDeleted'));
    }

     /**
      * Generates a URL from the given parameters.
      * @param  string  $name       The name of the route
      * @param  mixed   $parameters An array of parameters
      * @param  bool $absolute   Whether to generate an absolute URL
      * @return string  The generated URL
      */
     public static function generate($name, $parameters = array(), $absolute = false)
     {
         return Service::getGenerator()->generate($name, $parameters, $absolute);
     }

    /**
     * Gets the browser's request
     * @return Symfony\Component\HttpFoundation\Request
     */
    public static function getRequest()
    {
        return Service::getRequest();
    }

    /**
     * Gets the currently logged in player
     *
     * If the user is not logged in, a Player object that is invalid will be
     * returned
     *
     * @return Player
     */
    public static function getMe()
    {
        return Player::get(self::getRequest()->getSession()->get('playerId'));
    }

    /**
     * Find out whether debugging is enabled
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->container->getParameter('kernel.debug');
    }

    /**
     * Gets the monolog logger
     *
     * @param  string         $channel The log channel, defaults to the Controller's default
     * @return Monolog\Logger
     */
    protected static function getLogger($channel = null)
    {
        if (!$channel) {
            $channel = static::getLogChannel();
        }

        return Service::getContainer()->get("monolog.logger.$channel");
    }

    /**
     * Gets the logging channel for monolog
     * @return string
     */
    protected static function getLogChannel()
    {
        return 'app';
    }

    /**
     * Uses symfony's dispatcher to announce an event
     * @param  string $eventName The name of the event to dispatch.
     * @param  Event  $event     The event to pass to the event handlers/listeners.
     * @return Event
     */
    protected function dispatch($eventName, Event $event = null)
    {
        return $this->container->get('event_dispatcher')->dispatch($eventName, $event);
    }

    /**
     * Renders a view
     * @param  string $view       The view name
     * @param  array  $parameters An array of parameters to pass to the view
     * @return string The rendered view
     */
    protected function render($view, $parameters = array())
    {
        Debug::startStopwatch('view.render');

        $template = Service::getTemplateEngine();
        $ret = $template->render($view, $parameters);

        Debug::finishStopwatch('view.render');

        return $ret;
    }
}

class MissingArgumentException extends Exception
{
}
