<?php

abstract class HTMLController extends Controller {

    /**
     * Shows the HTML page header
     * @param string $title The `<title>` of the page to show
     */
    public function drawHeader($title) {
        $header = new Header($title);
        $header->draw();
    }

    /**
     * Shows an error page if the parameter is invalid
     * @todo 404 error code
     * @param Model $model The model to validate
     * @return boolean True if the model is valid, false if not
     */
    protected function validate($model) {
        if ($model->isValid())
            return true;

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
