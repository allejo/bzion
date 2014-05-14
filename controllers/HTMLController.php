<?php

abstract class HTMLController extends Controller {

    /**
     * Shows an error page if the parameter is invalid
     * @todo 404 error code
     * @todo Make this actually work
     * @param Model $model The model to validate
     * @return boolean True if the model is valid, false if not
     */
    protected function validate($model) {
        if ($model->isValid())
            return true;

        throw new Exception("Validations are broken for the time being");
        $this->forward("notFound", array("type" => $this->getName()));
        return false;
    }

    /**
     * Action that will be called if an object is not found
     * @param string type The type of the object (e.g Player)
     */
    public function notFoundAction($type) {
        return $this->render("notfound.html.twig",
               array ("type" => $type));
    }
}
