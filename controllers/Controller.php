<?php

abstract class Controller {

    /**
     * @var array
     */
    protected $parameters;

    /**
     *
     * @param array $parameters The array returned by $router->matchRequest()
     */
    public function __construct($parameters) {
        $this->parameters = $parameters;
    }

    /**
     * Returns the controller that is assigned to a route
     *
     * @param array $parameters The array returned by $router->matchRequest()
     * @return Controller The controller
     */
    public static function getController($parameters) {
        $ref = new ReflectionClass($parameters['_controller'] . 'Controller');

        return $ref->newInstance($parameters);
    }

    /**
     * Call the controller's action specified by the $parameters array
     *
     * @param string|null $action The action name to call, null to invoke the default one
     * @return void
     */
    public function callAction($action=null) {
        if (!$action)
            $action = $this->parameters['_action'];

        $this->setup();

        $ret = $this->callMethod($action . 'Action', $this->parameters);
        $this->handleResponse($ret, $action);

        $this->cleanup();
    }

    /**
     * Forward the request to another action
     *
     * @todo Forward the request to another controller
     * @param string $action The action to forward the request to
     * @param array $params An additional associative array of parameters to provide to the action
     * @return void
     */
    protected function forward($action, $params=array()) {
        $args = array_merge($this->parameters, $params);

        $ret = $this->callMethod($action . 'Action', $args);
        $this->handleResponse($ret, $action);
    }

    /**
     * Method that will be called before any action
     *
     * @return void
     */
    public function setup() {
    }

    /**
     * Method that will be called after all actions
     *
     * @return void
     */
    public function cleanup() {
    }

    /**
     * Call one of the controller's methods
     *
     * @param string $method The name of the method
     * @param array $parameters An associative array representing the method's parameters
     * @return mixed The return value of the called method
    */
    protected function callMethod($method, $parameters) {
        $ref = new ReflectionMethod($this, $method);
        $params = array();

        foreach ($ref->getParameters() as $p) {
            if ($model = $this->getModelFromParameters($p, $parameters)) {
                // The parameter's class is a Model
                // Get its slug from the request and send a Model to the method
                $params[] = $model;
            } else if (isset($parameters[$p->name])) {
                $params[] = $parameters[$p->name];
            } else if ($p->isOptional()) {
                $params[] = $p->getDefaultValue();
            } else {
                throw new MissingArgumentException("Missing parameter $p->name");
            }
        }

        return $ref->invokeArgs($this, $params);
    }

    /**
     * @param ReflectionParameter $modelParameter
     */
    private function getModelFromParameters($modelParameter, $routeParameters) {
        $refClass = $modelParameter->getClass();
        $paramName  = $modelParameter->getName();

        if ($refClass === null)
            return null;

        if ($refClass->getName() == "Symfony\Component\HttpFoundation\Request")
            return $this->getRequest();

        if ($refClass->getName() == "Symfony\Component\HttpFoundation\Session\Session")
            return $this->getRequest()->getSession();

        if (!$refClass->isSubclassOf("Model"))
            return null;

        if (is_object($routeParameters[$paramName]) &&
            $refClass->getName() === get_class($routeParameters[$paramName])
           ) {
            // The model has already been instantiated - we don't need to do anything
            return $routeParameters[$paramName];
        }

        // Look for the object's ID/slugs in the routeParameters array
        if (isset($routeParameters[$paramName]))
            return $refClass->getMethod("fetchFromSlug")->invoke(null, $routeParameters[$paramName]);

        if (isset($routeParameters[$paramName . 'Id']))
            return $refClass->newInstance($routeParameters[$paramName . 'Id']);
    }

    private function handleResponse($response, $action) {
        if (is_array($response)) {
            // The controller is probably expecting us to show a view to the
            // user, using the array provided to set variables for the template
            $templatePath = $this->getName() . "/$action.html.twig";

            echo $this->render($templatePath, $response);
        } else if (is_string($response)) {
            echo $response;
        }
    }

    /**
     * Returns the name of the controller without the "Controller" part
     * @return string
     */
    protected function getName() {
        return preg_replace('/Controller$/', '', get_called_class());
    }

    /**
     * Generates a URL from the given parameters.
     * @param string $name The name of the route
     * @param mixed $parameters An array of parameters
     * @param boolean $absolute Whether to generate an absolute URL
     * @return string The generated URL
     */
     public static function generate($name, $parameters = array(), $absolute = false) {
        return Service::getGenerator()->generate($name, $parameters, $absolute);
     }

     /**
      * Gets the browser's request
      * @return Symfony\Component\HttpFoundation\Request
      */
    public static function getRequest() {
        return Service::getRequest();
    }

    /**
     * Renders a view
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @return string The rendered view
     */
    protected function render($view, $parameters=array()) {
        $template = Service::getTemplateEngine();
        return $template->render($view, $parameters);
    }
}


class MissingArgumentException extends Exception {
}
