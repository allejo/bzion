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

    /**
     * Edit a profile
     *
     * @param  Player  $me      The player's profile to edit
     * @param  Request $request
     * @param  bool $self    Whether a player is editing their own profile,
     *                          instead of an admin editing another player's
     *                          profile
     * @return array
     */
    public function editAction(Player $me, Request $request, $self = true)
    {
        $creator = new ProfileFormCreator($me);
        $creator->setEditingSelf($self);
        $form = $creator->create()->handleRequest($request);

        if ($form->isValid()) {
            if (!$self && $form->has('verify_email') && $form->get('verify_email')->isClicked()) {
                // An admin is editing a form and has chosen to verify a
                // player's e-mail address
                $me->setVerified(true);

                // Reset the form so that the "verify email" button gets hidden
                $form = $creator->create()->handleRequest($request);
            } else {
                $creator->update($form, $me);

                $email = $form->get('email')->getData();
                if ($email !== $me->getEmailAddress()) {
                    // User has changed their address, send a confirmation mail
                    $me->setEmailAddress($email);

                    if ($self) {
                        $this->sendConfirmationMessage($me);
                    } else {
                        // Admins can set users' e-mail addresses at will, without
                        // having to send them confirmation messages
                        $me->setVerified(true);
                    }
                }
            }

            $message = ($self) ? "Your profile has been updated." : $me->getUsername() . "'s profile has been updated.";
            $this->getFlashBag()->add("success", $message);
        }

        return $this->render('Profile/edit.html.twig', array(
            "editingSelf" => $self,
            "player"      => $me,
            "form"        => $form->createView()
        ));
    }

    /**
     * @todo Expire verification codes
     */
    public function confirmAction(Player $me, $token)
    {
        if (!$me->getEmailAddress()) {
            throw new ForbiddenException("You need to have an e-mail address to confirm!");
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

    /**
     * Send a confirmation e-mail to a player
     * @param Player $player The receiving player
     */
    private function sendConfirmationMessage($player)
    {
        if ($player->getConfirmCode() === null) {
            // The player has no confirmation code, don't send them a message
            return;
        }

        $from = $this->container->getParameter('bzion.email.from');
        $title = $this->container->getParameter('bzion.site.name');

        if (!$from) {
            $this->getLogger()->addError('Unable to send verification e-mail message to player due to the "From:" address not being specified', array(
                'player' => array('id' => $player->getId(), 'username' => $player->getUsername())
            ));
            return;
        }

        $message = Swift_Message::newInstance()
            ->setSubject($title . ' Email Confirmation')
            ->setFrom(array($from => $title))
            ->setTo($player->getEmailAddress())
            ->setBody($this->render('Email/confirm.txt.twig',  array('player' => $player)))
            ->addPart($this->render('Email/confirm.html.twig', array('player' => $player)), 'text/html');

        $this->container->get('mailer')->send($message);

        $this->getFlashBag()->add('info',
            'Please check your inbox in order to confirm your email address.');
    }
}
