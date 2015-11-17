<?php

use Symfony\Component\HttpFoundation\Response;

/**
 * A controller that will not connect to Twig, used for API calls
 *
 * @package BZiON\Controllers
 */
abstract class PlainTextController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function callAction($action = null)
    {
        try {
            return parent::callAction($action);
        } catch (HTTPException $e) {
            return new Response($e->getMessage());
        } catch (Exception $e) {
            // Let PHP handle the exception on the dev environment
            if ($this->isDebug()) {
                throw $e;
            }
            return new Response("An error occured");
        }
    }
}
