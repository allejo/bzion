<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
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
            return $this->forward("NotFound", array("exception" => $e));
        } catch (HTTPException $e) {
            return $this->forward("Error", array(
                                           "message" => $e->getMessage(),
                                           "code" => $e->getErrorCode()));
        } catch (Exception $e) {
            // Let PHP handle the exception on the dev environment
            if (DEVELOPMENT) throw $e;
            return $this->forward("Error", array("message" => "An error occured"));
        }
    }

    /**
     * Action that will be called if an object is not found
     * @param ModelNotFoundException $exception The exception
     */
    public function notFoundAction(ModelNotFoundException $exception)
    {
        return new Response(
            $this->render("notfound.html.twig",
                    array("message" => $exception->getMessage(),
                          "type" => $exception->getType()
                    )),
            404);
    }

    /**
     * @param string $message The message to show
     * @param int    $code    The message's HTTP code
     */
    public function errorAction($message, $code=500)
    {
        return new Response(
            $this->render("error.html.twig",array("message" => $message)),
            $code
        );
    }

    /**
     * Returns a redirect response to the previous page
     * @return RedirectResponse
     */
    protected function goBack() {
        // If the request's headers had an HTTP_REFERER parameter, go back there
        // Otherwise just redirect the user to the home page
        $loc = $this->getRequest()->server->get('HTTP_REFERER',
                                   Service::getGenerator()->generate('index'));

        return new RedirectResponse($loc);
    }
}
