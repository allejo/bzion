<?php

use Symfony\Component\HttpFoundation\Response;


abstract class HTMLController extends Controller {

    protected function getModelFromParameters($modelParameter, $routeParameters) {
        $model = parent::getModelFromParameters($modelParameter, $routeParameters);

        if (!$model instanceof Model || $model->isValid())
            return $model;

        else
            throw new ModelNotFoundException($model->getParamName());
    }

    public function callAction($action=null) {
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
    public function notFoundAction($type) {
        return new Response(
            $this->render("notfound.html.twig", array("type" => $type)),
            404);
    }
}

class ModelNotFoundException extends Exception {
}
