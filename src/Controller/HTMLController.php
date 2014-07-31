<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package BZiON\Controllers
 */
abstract class HTMLController extends Controller
{
    /**
     * Whether twig has been prepared
     * @var boolean
     */
    private $twigReady = false;

    /**
     * Prepare the twig global variables
     */
    private function addTwigGlobals()
    {
        if ($this->twigReady)
            return;

        $request = $this->getRequest();

        // Add global variables to the twig templates
        $twig = Service::getTemplateEngine();
        $twig->addGlobal("request", $request);
        $twig->addGlobal("session", $request->getSession());
        $twig->addGlobal("pages",      Page::getPages());
        $twig->addGlobal("controller", $this);
        $twig->addGlobal("me",         $this->getMe());

        $this->prepareTwig();

        $this->twigReady = true;
    }

    protected function prepareTwig()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function render($view, $parameters=array())
    {
        $this->addTwigGlobals();

        return parent::render($view, $parameters);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ModelNotFoundException
     */
    protected function findModelInParameters($modelParameter, $routeParameters)
    {
        $model = parent::findModelInParameters($modelParameter, $routeParameters);

        if (!$model instanceof Model || !$model->isDeleted())
            return $model;
        elseif ($modelParameter->getName() !== "me") {
            throw new ModelNotFoundException($model->getTypeForHumans());
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function callAction($action=null)
    {
        try {
            $response = parent::callAction($action);
            if (!$response->isRedirection())
                $this->saveURL();

            return $response;
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
     * Save the URL of the current page so that the user can be redirected back to it
     * if they login
     */
    protected function saveURL()
    {
        $session = $this->getRequest()->getSession();

        $urls = $session->get('previous_paths', array());
        array_unshift($urls, $this->getRequest()->getPathInfo());

        // No need to have more than 4 urls stored on the array
        while (count($urls) > 4)
            array_pop($urls);

        // Store the URLs in the session, removing any duplicate entries
        $session->set('previous_paths', array_unique($urls));
    }

    /*
     * Returns the path to the home page
     * @return string
     */
    protected function getHomeURL()
    {
        return Service::getGenerator()->generate('index');
    }

    /*
     * Returns the URL of the previous page
     * @return string
     */
    protected function getPreviousURL()
    {
        $request = $this->getRequest();

        $urls = $request->getSession()->get('previous_paths', array());
        foreach ($urls as $url)
            if ($url != $request->getPathInfo()) // Don't redirect to the same page
                return $request->getBasePath() . $url;

        // No stored URLs found, just redirect them to the home page
        return $this->getHomeURL();
    }

    /*
     * Returns a redirect response to the previous page
     * @return RedirectResponse
     */
    protected function goBack()
    {
        return new RedirectResponse($this->getPreviousURL());
    }

    /**
     * Returns a redirect response to the home page
     * @return RedirectResponse
     */
    protected function goHome()
    {
        return new RedirectResponse($this->getHomeURL());
    }

    /**
     * Get the session's flash bag
     * @return Symfony\Component\HttpFoundation\Session\Flash\FlashBag
     */
    public static function getFlashBag()
    {
        return self::getRequest()->getSession()->getFlashBag();
    }

    /*
     * Assert that the user is logged in
     * @throws HTTPException
     * @param  string        $message The message to show if the user is not logged in
     * @return void
     */
    protected function requireLogin(
        $message="You need to be signed in to do this"
    ) {
        $me = new Player($this->getRequest()->getSession()->get('playerId'));

        if (!$me->isValid())
            throw new ForbiddenException($message);
    }

    /*
     * Show a confirmation (Yes, No) form to the user
     *
     * @param  callable $onYes          What to do if the user clicks on "Yes"
     * @param  string   $message        The message to show to the user, asking them to confirm their action
     * @param  string   $action         The text to show on the "Yes" button
     * @param  string   $successMessage A message to add on the session's flashbag on success
     * @param  callable $onNo           What to do if the user presses "No" - defaults to
     *                                  redirecting them back
     * @return mixed    The response
     */
    protected function showConfirmationForm(
        $onYes,
        $message = "Are you sure you want to do this?",
        $successMessage = "Operation completed successfully",
        $action = "Yes",
        $onNo = null
    ) {
        $form = Service::getFormFactory()->createBuilder()
            ->add($action, 'submit')
            ->add(($action == 'Yes') ? 'No' : 'Cancel', 'submit')
            ->add('original_url', 'hidden', array(
                'data' => $this->getPreviousURL()
            ))
            ->getForm();

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            if ($form->get($action)->isClicked()) {
                $return = $onYes();

                // If no exceptions are thrown, show a success message
                $this->getFlashBag()->add('success', $successMessage);

                return $return;
            } elseif (!$onNo) {
                // We didn't get told about what to do when the user presses no,
                // just get them back where they were
                return new RedirectResponse($form->get('original_url')->getData());
            } else {
                return $onNo();
            }
        }

        return $this->render('confirmation.html.twig', array(
            'form' => $form->createView(),
            'message' => $message
        ));
    }
}
