<?php

use Symfony\Component\HttpFoundation\JsonResponse;

class MaintenanceController extends JSONController
{
    public function showAction()
    {
        $text = $this->container->getParameter('bzion.miscellaneous.maintenance');

        if (is_string($text)) {
            $defaultText = $text;
        } else {
            $defaultText = "Services are currently down for maintenance. Please try again later.";
        }

        if ($this->isJson()) {
            return new JsonResponse(array(
                'success' => false,
                'content' => $defaultText
            ));
        }

        // Return plain text if we were using a controller that didn't return
        // HTML
        $attributes = $this->getRequest()->attributes;
        if ($attributes->has('_controller')) {
            if (\Controller::getController($attributes) instanceof \PlainTextController) {
                return $defaultText;
            }
        }

        return array(
            'text' => $text
        );
    }
}
