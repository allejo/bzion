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
        return $this->callMethod($this->parameters['_action'] . 'Action', $this->parameters);
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
            if ($p->isOptional()) {
                if(isset($parameters[$p->name])) {
                    $params[] = $parameters[$p->name];
                } else {
                    $params[] = $p->getDefaultValue();
                }
            } else if (isset($parameters[$p->name])) {
                $params[] = $parameters[$p->name];
            } else {
                throw new MissingArgumentException("Missing parameter $p->name");
            }
        }

        return $ref->invokeArgs($this, $params);
    }

}


class MissingArgumentException extends Exception {
}
