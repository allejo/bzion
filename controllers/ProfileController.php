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
            $me->setEmailAddress($form->get('email')->getData());
            $me->setCountry($form->get('country')->getData());
        }

        return array("player" => $me, "form" => $form->createView());
    }

    public function confirmAction(Player $me, $token)
    {
        if (!$me->getEmailAddress()) {
            throw new ForbiddenException("You need to have an e-mail address to confirm it!");
        }

        if ($me->isVerified()) {
            throw new ForbiddenException("You have already been verified");
        }

        if (!$me->isCorrectConfirmCode($token)) {
            throw new ForbiddenException("Invalid verification code");
        }

        $me->setVerified(true);

        $this->getFlashBag()->add('success', "Your e-mail address has been successfully verified");
        return new RedirectResponse($me->getUrl());
    }

    public function showAction(Player $me)
    {
        return new RedirectResponse($me->getUrl());
    }
}
