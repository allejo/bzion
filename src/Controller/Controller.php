<?php

/**
 * This file contains the skeleton for all of the controllers
 *
 * @package    BZiON\Controllers
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

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
abstract class Controller
{
    /**
     * Parameters specified by the route
     * @var ParameterBag
     */
    protected $parameters;

    /**
     *
     * @param ParameterBag $parameters The array returned by $router->matchRequest()
     */
    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns the controller that is assigned to a route
     *
     * @param  ParameterBag $parameters The array returned by $router->matchRequest()
     * @return Controller   The controller
     */
    public static function getController($parameters)
    {
        $ref = new ReflectionClass($parameters->get('_controller') . 'Controller');

        return $ref->newInstance($parameters);
    }

    /**
     * Call the controller's action specified by the $parameters array
     *
     * @param  string|null $action The action name to call, null to invoke the default one
     * @return Response    The action's response
     */
    public function callAction($action=null)
    {
        if (!$action)
            $action = $this->parameters->get('_action');

        $this->setup();
        $response = $this->forward($action);
        $this->cleanup();

        return $response->prepare($this->getRequest());
    }

    /**
     * Forward the request to another action
     *
     * Please note that this doesn't generate an HTTP redirect, but an
     * internal one - the user sees the original URL, but a different page
     *
     * @todo Forward the request to another controller
     * @param  string   $action The action to forward the request to
     * @param  array    $params An additional associative array of parameters to provide to the action
     * @return Response
     */
    protected function forward($action, $params=array())
    {
        $args = clone $this->parameters;
        $args->add($params);

        $ret = $this->callMethod($action . 'Action', $args);

        return $this->handleReturnValue($ret, $action);
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
     * @param  string       $method     The name of the method
     * @param  ParameterBag $parameters The parameter bag representing the route's parameters
     * @return mixed        The return value of the called method
    */
    protected function callMethod($method, $parameters)
    {
        $ref = new ReflectionMethod($this, $method);
        $params = array();

        foreach ($ref->getParameters() as $p) {
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

        return $ref->invokeArgs($this, $params);
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

        // $me -> currently logged in user
        if ($paramName == "me")
            return $refClass->newInstance(Service::getSession()->get('playerId'));

        if ($refClass === null)
            // No class provived by the method's definition, we don't know
            // what we should pass
            return null;

        switch ($refClass->getName()) {
        case "Symfony\Component\HttpFoundation\Request":
            return $this->getRequest();
        case "Symfony\Component\HttpFoundation\Session\Session":
            return $this->getRequest()->getSession();
        case "Symfony\Component\HttpFoundation\Session\Flash\FlashBag":
            return $this->getRequest()->getSession()->getFlashBag();
        case "Symfony\Component\Form\FormFactory":
            return Service::getFormFactory();
        }

        if ($refClass->isSubclassOf("Model"))
            // Look for the object's ID/slugs in the routeParameters array
            return $this->findModelInParameters($modelParameter, $routeParameters);

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

        if ($routeParameters->has($paramName . 'Id'))
            return $refClass->newInstance($routeParameters->get($paramName . 'Id'));
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
        if ($return instanceof Response)
            return $return;

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
    protected function getName()
    {
        return preg_replace('/Controller$/', '', get_called_class());
    }

    /**
     * Generates a URL from the given parameters.
     * @param  string  $name       The name of the route
     * @param  mixed   $parameters An array of parameters
     * @param  boolean $absolute   Whether to generate an absolute URL
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
     * Renders a view
     * @param  string $view       The view name
     * @param  array  $parameters An array of parameters to pass to the view
     * @return string The rendered view
     */
    protected function render($view, $parameters=array())
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
