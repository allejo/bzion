<?php

use Symfony\Component\HttpFoundation\Response;

/**
 * @package BZiON\Controllers
 */
abstract class HTMLController extends Controller
{
    /**
     * {@inheritDoc}
     *
     * @throws ModelNotFoundException
     */
    protected function findModelInParameters($modelParameter, $routeParameters)
    {
        $model = parent::findModelInParameters($modelParameter, $routeParameters);

        if (!$model instanceof UrlModel || $model->isValid())
            return $model;
        elseif ($modelParameter->getName() !== "me")
            throw new ModelNotFoundException($model->getParamName());

        return $model;
    }

    public function callAction($action=null)
    {
        try {
            return parent::callAction($action);
        } catch (ModelNotFoundException $e) {
            return $this->forward("NotFound", array("type" => $e->getMessage()));
        }
    }

    /**
     * Action that will be called if an object is not found
     * @param string type The type of the object (e.g Player)
     */
    public function notFoundAction($type)
    {
        return new Response(
            $this->render("notfound.html.twig", array("type" => $type)),
            404);
    }
}

class ModelNotFoundException extends Exception
{
}
