<?php

use BZIon\Form\Creator\ProfileFormCreator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends HTMLController
{
    public function setup()
    {
        $this->requireLogin();
    }

    public function editAction(Player $me, Request $request)
    {
        $creator = new ProfileFormCreator($me);
        $form    = $creator->create()->handleRequest($request);

        if ($form->isValid()) {
            $me->setDescription($form->get('description')->getData());
            $me->setTimezone($form->get('timezone')->getData());
            $me->setCountry($form->get('country')->getData());
        }

        return array("player" => $me, "form" => $form->createView());
    }

    public function showAction(Player $me)
    {
        return new RedirectResponse($me->getUrl());
    }
}
