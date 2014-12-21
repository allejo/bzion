<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * A controller with the capability of responding with JSON data
 * @package BZiON\Controllers
 */
abstract class JSONController extends HTMLController
{
    /**
     * Values to be included in the JSON response
     */
    protected $attributes;

    /**
     * {@inheritDoc}
     */
    public function __construct($parameters)
    {
        $this->attributes = new ParameterBag();

        parent::__construct($parameters);
    }

    /**
     * Finds whether the client has requested a JSON document
     *
     * @return bool
     **/
    protected function isJson()
    {
        $request = $this->getRequest();
        foreach (array($request->request, $request->query) as $params) {
            if (strtolower($params->get('format')) == 'json') {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function handleReturnValue($return, $action)
    {
        // Format strings nicely with JSON, if the client wants that
        if ($this->isJson()) {
            if (!$return instanceof JsonResponse) {
                $response = array('success' => true);

                $flashbag = $this->getFlashBag();
                if ($flashbag->has('success')) {
                    $messages = $flashbag->get('success');
                    $response['message'] = $messages[0];
                }

                $response['content'] = (is_array($return))
                                     ? $this->renderDefault($return, $action)
                                     : $return;

                $response = array_replace($response, $this->attributes->all());

                $return = new JsonResponse($response);
            }
        }

        return parent::handleReturnValue($return, $action);
    }
}
