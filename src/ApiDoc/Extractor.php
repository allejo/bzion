<?php
/**
 * This file contains functionality relating to API documentation
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\ApiDoc;

use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Route;

/**
 * A class that finds the ApiDoc annotations in our controllers
 */
class Extractor extends ApiDocExtractor
{
    /**
     * Returns an array of data where each data is an array with the following keys:
     *  - annotation
     *  - resource
     *
     * @param array  $routes array of Route-objects for which the annotations should be extracted
     * @param string $view
     *
     * @return array
     */
    public function extractAnnotations(array $routes, $view = 'default')
    {
        foreach ($routes as &$route) {
            if (!$route instanceof Route) {
                throw new \InvalidArgumentException(sprintf('All elements of $routes must be instances of Route. "%s" given', gettype($route)));
            }

            if (!$route->getDefault('_defaultHandler')) {
                $route = clone $route;

                $parameters = new ParameterBag($route->getDefaults());
                $route->setDefault('_controller', \Controller::getController($parameters)->getAction());
            }
        }

        return parent::extractAnnotations($routes);
    }

    /**
     * Returns the ReflectionMethod for the given controller string.
     *
     * @param  \ReflectionMethod|string $controller
     * @return \ReflectionMethod|null
     */
    public function getReflectionMethod($controller)
    {
        if ($controller instanceof \ReflectionMethod) {
            return $controller;
        } else {
            return parent::getReflectionMethod($controller);
        }
    }
}
