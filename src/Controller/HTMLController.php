<?php

use BZIon\Form\Creator\ConfirmationFormCreator;
use BZIon\Twig\AppGlobal;
use BZIon\Twig\ModelFetcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package BZiON\Controllers
 */
abstract class HTMLController extends Controller
{
    /**
     * Whether twig has been prepared
     * @var bool
     */
    public $twigReady = false;

    /**
     * Prepare the twig global variables
     */
    private function addTwigGlobals()
    {
        if ($this->twigReady) {
            return;
        }

        $request = $this->getRequest();

        // Add global variables to the twig templates
        $twig = $this->container->get('twig');
        $twig->addGlobal("me",      $this->getMe());
        $twig->addGlobal("model",   new ModelFetcher());
        $twig->addGlobal("request", $request);
        $twig->addGlobal("session", $request->getSession());

        $twig->addGlobal("app", new AppGlobal($this->parent, $this->container));

        $this->prepareTwig();

        $this->twigReady = true;
    }

    protected function prepareTwig()
    {
    }

    /**
     * {@inheritdoc}
     * @param string $view
     */
    protected function render($view, $parameters = array())
    {
        $this->addTwigGlobals();

        return parent::render($view, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelNotFoundException
     */
    protected function findModelInParameters($modelParameter, $routeParameters)
    {
        $model = parent::findModelInParameters($modelParameter, $routeParameters);

        if (!$model instanceof Model || $modelParameter->getName() === "me") {
            // `$me` can be invalid if, for example, no user is currently logged
            // in - in this case we can just pass the invalid Player model to
            // the controller without complaining
            return $model;
        } elseif (!$this->canSee($model)) {
            // If the model is not supposed to be visible to the player
            // requesting it, pretend it's not there
            throw new ModelNotFoundException($model->getTypeForHumans());
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function callAction($action = null)
    {
        $response = parent::callAction($action);
        if (!$response->isRedirection()
            && !$response->isNotFound()
            && !$this->getRequest()->isXmlHttpRequest()) {
            $this->saveURL();
        }

        return $response;
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
        while (count($urls) > 4) {
            array_pop($urls);
        }

        // Store the URLs in the session, removing any duplicate entries
        $session->set('previous_paths', array_unique($urls));
    }

    /**
     * Returns the path to the home page
     * @return string
     */
    protected function getHomeURL()
    {
        return Service::getGenerator()->generate('index');
    }

    /**
     * Returns the URL of the previous page
     * @return string
     */
    protected function getPreviousURL()
    {
        $request = $this->getRequest();

        $urls = $request->getSession()->get('previous_paths', array());
        foreach ($urls as $url) {
            if ($url != $request->getPathInfo()) {
                // Don't redirect to the same page
                return $request->getBaseUrl() . $url;
            }
        }

        // No stored URLs found, just redirect them to the home page
        return $this->getHomeURL();
    }

    /**
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

    /**
     * Find out whether the currently logged in user can see a model
     *
     * Apart from the permissions of the user, this method takes the request
     * query into consideration to find out if the user wants to see deleted
     * models or not.
     *
     * @param  Model Model Model in question
     * @return bool
     */
    public static function canSee($model)
    {
        if (!$model instanceof PermissionModel) {
            return !$model->isDeleted();
        }

        return static::getMe()->canSee($model, static::getRequest()->get('showDeleted'));
    }

    /**
     * Assert that the user is logged in
     * @param  string        $message The message to show if the user is not logged in
     * @throws HTTPException
     * @return void
     */
    protected function requireLogin(
        $message = "You need to be signed in to do this"
    ) {
        if (!$this->getMe()->isValid()) {
            throw new ForbiddenException($message);
        }
    }

    /**
     * Show a confirmation (Yes, No) form to the user
     *
     * @param  callable $onYes          What to do if the user clicks on "Yes"
     * @param  string   $message        The message to show to the user, asking
     *                                  them to confirm their action (RAW text
     *                                  is shown - don't forget to properly
     *                                  escape your parameters!)
     * @param  string   $action         The text to show on the "Yes" button
     * @param  string   $successMessage A message to add on the session's
     *                                  flashbag on success (flashbags
     *                                  automatically escape text)
     * @param  callable $onNo           What to do if the user presses "No" -
     *                                  defaults to redirecting them back
     * @param  string   $view           The view to redirect to
     * @param  bool  $noButton       Whether to show a "No" instead of a
     *                                  "Cancel" button
     * @return mixed    The response
     */
    protected function showConfirmationForm(
        $onYes,
        $message = "Are you sure you want to do this?",
        $successMessage = "Operation completed successfully",
        $action = "Yes",
        $onNo = null,
        $view = 'confirmation.html.twig',
        $noButton = false
    ) {
        $creator = new ConfirmationFormCreator($action, $this->getPreviousURL(), $noButton);
        $form = $creator->create()->handleRequest($this->getRequest());

        if ($form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $return = $onYes();

                // If no exceptions are thrown, show a success message
                $this->getFlashBag()->add('success', $successMessage);

                return $return;
            } elseif (!$onNo) {
                // We didn't get told about what to do when the user presses no,
                // just get them back where they were
                return new RedirectResponse($form->get('original_url')->getData());
            } else {
                return $onNo($form->get('original_url')->getData());
            }
        }

        return $this->render($view, array(
            'form'    => $form->createView(),
            'message' => $message
        ));
    }

    /**
     * Decompose a list of object IDs into the corresponding IDs
     *
     * @param string $query  The user's query
     * @param array  $types  A list of the acceptable model types (will NOT be sanitized)
     * @param bool   $models Whether to return an array of models instead of an array of IDs
     * @param int|null $max  The largest number of models to accept, or null for infinite models
     *
     * @throws BadRequestException
     */
    protected function decompose($query, array $types, $models = true, $max = null)
    {
        $query = explode(',', $query);

        if ($max !== null && count($query) > $max) {
            throw new \BadRequestException("Too many objects provided");
        }

        $result = array();
        $firstType = reset($types);

        if (!$models) {
            // Make sure the result array has a subarray for each type
            foreach ($types as $type) {
                $result[$type] = array();
            }
        }

        foreach ($query as $object) {
            if ($object === '') {
                continue;
            }

            $object = explode(':', $object, 3);
            if (count($object) === 2) {
                $class = ucfirst($object[0]);
                $id = (int) $object[1];

                if (!in_array($class, $types)) {
                    throw new \BadRequestException("Invalid object type");
                }

                if ($models) {
                    $this->assertVisibility($result[] = $class::get($id));
                } else {
                    $result[$class][] = $id;
                }
            } elseif (count($object) === 1) {
                // No type was provided
                if (count('types') > 1) {
                    throw new \BadRequestException(
                        "You need to provide the type of the object"
                    );
                }

                if ($models) {
                    $this->assertVisibility($result[] = $firstType::get($id));
                } else {
                    $result[$firstType][] = (int) $object[0];
                }
            } else {
                throw new \BadRequestException("Malformed object");
            }
        }

        return $result;
    }

    /**
     * Throw an innocent exception if a player can't see a Model or if it
     * doesn't exist
     *
     * @param $model The model to test
     *
     * @throws BadRequestException
     */
    private function assertVisibility(PermissionModel $model)
    {
        if (!$this->getMe()->canSee($model)) {
            throw new \BadRequestException("Invalid object provided");
        }
    }
}
