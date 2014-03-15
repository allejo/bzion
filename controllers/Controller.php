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
     * @return mixed The return value of the called action
    */
    public function callAction() {
        $this->setup();
        $ret = $this->callMethod($this->parameters['_action'] . 'Action', $this->parameters);
        $this->cleanup();
        return $ret;
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
        $params = [];

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

    private function getModelFromParameters($modelParameter, $routeParameters) {
        $refClass = $modelParameter->getClass();

        if ($refClass === null)
            return null;
        if (!$refClass->isSubclassOf("Model"))
            return null;

        // Look for the object's ID/slugs in the routeParameters array
        if (isset($routeParameters[$modelParameter->getName()]))
            return $refClass->getMethod("fetchFromSlug")->invoke(null, $routeParameters[$modelParameter->getName()]);

        if (isset($routeParameters[$modelParameter->getName() . 'Id']))
            return $refClass->newInstance($routeParameters[$modelParameter->getName() . 'Id']);
    }

}


class MissingArgumentException extends Exception {
}
