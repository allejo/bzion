<?php

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorController extends HTMLController
{
    /**
     * Show an error message, provided that the ecxception is stored in the
     * request's attributes
     *
     * @return mixed
     */
    public function errorAction()
    {
        $exception = $this->getRequest()->attributes->get('exception');

        if ($exception instanceof ModelNotFoundException) {
            return $this->modelNotFoundAction($exception->getType(), $exception->getMessage());
        } elseif ($exception instanceof NotFoundHttpException) {
            return $this->genericErrorAction('Sorry, the page you are looking for could not be found.');
        } elseif ($exception instanceof HTTPException) {
            return $this->genericErrorAction($exception->getMessage());
        }  else {
            return $this->genericErrorAction();
        }
    }

    /**
     * Show a generic error message
     *
     * @param  string $message The error message to show
     * @return array
     */
    public function genericErrorAction($message = 'An error occured')
    {
        return $this->render('Error/genericError.html.twig', array(
            'message' => $message
        ));
    }

    /**
    * Show an error message for a model that wasn't found
    *
    * @param  string $type    The type of the missing model
    * @param  string $message The error message to show
    * @return array
    */
    public function modelNotFoundAction($type = '', $message = 'The specified model was not found')
    {
        return $this->render('Error/modelNotFound.html.twig', array(
            "message" => $message,
            "type" => $type
        ));
    }
}
