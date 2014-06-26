<?php

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * A controller with the capability of responding with JSON data
 * @package BZiON\Controllers
 */
abstract class JSONController extends HTMLController
{
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
    public function notFoundAction(ModelNotFoundException $exception)
    {
        if (!$this->isJson())
            return parent::notFoundAction($exception);
        return $this->errorAction($exception->getMessage(), 404);
    }

    /**
     * {@inheritDoc}
     */
    public function errorAction($message, $code=500)
    {
        if (!$this->isJson())
            return parent::errorAction($message, $code);

        return new JSONResponse(array(
            "success" => false,
            "message" => $message,
        ), $code);
    }

    /**
     * {@inheritDoc}
     */
    protected function handleReturnValue($return, $action)
    {
        // Format strings nicely with JSON, if the client wants that
        if ($this->isJson()) {
            if (!$return instanceof JsonResponse) {
                $response = array("success" => true);

                $flashbag = $this->getRequest()->getSession()->getFlashBag();
                if ($flashbag->has('success')) {
                    $messages = $flashbag->get('success');
                    $response['message'] = $messages[0];
                }

                $response['content'] = (is_array($return))
                                     ? $this->renderDefault($return, $action)
                                     : $return;

                $return = new JsonResponse($response);
            }
        }

        return parent::handleReturnValue($return, $action);
    }
}
