<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageController extends JSONController
{
    public function setup()
    {
        $this->requireLogin();

        if (!$this->isJson()) {
            $groups = Group::getGroups($this->getRequest()->getSession()->get("playerId"));
            Service::getTemplateEngine()->addGlobal("groups", $groups);
        }
    }

    public function composeAction(Player $me, Request $request)
    {
        if (!$me->hasPermission(Permission::SEND_PRIVATE_MSG))
            throw new ForbiddenException("You are not allowed to send messages");

        $notBlank = array( 'constraints' => new NotBlank() );
        $form = Service::getFormFactory()->createBuilder()
            ->add('Recipients', 'text', $notBlank) // Comma-separated list of recipients
            ->add('Subject', 'text', $notBlank)
            ->add('Message', 'textarea', $notBlank)
            ->add('ListUsernames', 'hidden', array(
                'data' => true, // True if the client provided the recipient usernames
            ))                  // instead of IDs (to support non-JS browsers)
            ->add('Send', 'submit')
            // Prevents JS from going crazy if we load a page with AJAX
            ->setAction(Service::getGenerator()->generate('message_list'))
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $recipients = $this->validateComposeForm($form, $me);
            if ($form->isValid()) {
                $subject = $form->get('Subject')->getData();
                $content = $form->get('Message')->getData();

                $group_to = Group::createGroup($subject, $me->getId(), $recipients);
                Message::sendMessage($group_to->getId(), $me->getId(), $content);

                if ($this->isJson())
                    return new JsonResponse(array(
                        'success' => true,
                        'message' => 'Your message was sent successfully',
                        'id'      => $group_to->getId()
                    ));
                else return new RedirectResponse($group_to->getUrl());
            } elseif ($this->isJson())
                throw new BadRequestException($this->getErrorMessage($form));
        }

        return array("form" => $form->createView(), "players" => Player::getPlayers());
    }

    public function showAction(Group $discussion, Player $me, Request $request)
    {
        $this->assertCanParticipate($me, $discussion);

        // Create the form to send a message to the discussion
        $form = Service::getFormFactory()->createBuilder()
            ->add('message', 'textarea')
            ->add('Send', 'submit')
            ->setAction($discussion->getUrl())
            ->getForm();

        // Keep a cloned version so we can come back to it later, if we need
        // to reset the fields of the form
        $cloned = clone $form;
        $form->handleRequest($request);

        if ($form->isValid()) {
            // The player wants to send a message
            $this->sendMessage($me, $discussion, $form, $cloned);

            if ($this->isJson())
                return "Your message was sent successfully";
        }

        $messages = Message::getMessages($discussion->getId());

        return array("form" => $form->createView(), "group" => $discussion, "messages" => $messages);
    }

    /**
     * Make sure that a player can participate in a group
     *
     * Throws an exception if a player is not an admin or a member of that group
     * @todo Permission for spying on other people's groups?
     * @throws HTTPException
     * @param  Player        $player  The player to test
     * @param  Group         $group   The message group
     * @param  string        $message The error message to show
     * @return void
     */
    private function assertCanParticipate(Player &$player, Group &$group,
        $message="You are not allowed to participate in that discussion"
    ) {
        if (!$group->isMember($player->getId()))
            throw new ForbiddenException($message);
    }

    /**
     * Sends a message to a group
     *
     * @throws HTTPException Thrown if the user doesn't have the
     *                               SEND_PRIVATE_MSG permission
     * @param  Player        $from    The sender
     * @param  Group         $to      The group that will receive the message
     * @param  Form          $form    The message's form
     * @param  Form          $form    The form before it handled the request
     * @param  string        $message The message to send
     * @return void
     */
    private function sendMessage(Player &$from, Group &$to, &$form, &$cloned)
    {
        if (!$from->hasPermission(Permission::SEND_PRIVATE_MSG))
            throw new ForbiddenException("You are not allowed to send messages");

        $message = $form->get('message')->getData();

        if (trim($message) == '')
            throw new BadRequestException("You can't send an empty message!");

        Message::sendMessage($to->getId(), $from->getId(), $message);

        $this->success("Your message was sent successfully");

        // Reset the form
        $form = $cloned;
    }

    private function validateComposeForm(&$form, Player &$me)
    {
        $recipients = explode(',', $form->get('Recipients')->getData());
        $listingUsernames = (bool) $form->get('ListUsernames')->getData();
        $recipientIds = array();

        // Remove all the whitespace and duplicate entries
        $recipients = array_map(function ($r) { return trim($r); }, $recipients);
        $recipients = array_unique($recipients);

        foreach ($recipients as $rid) {
            if (empty($rid)) continue;

            if ($listingUsernames) {
                $recipient = Player::getFromUsername($rid);
            } else {
                $recipient = new Player($rid);
            }

            if ($recipient->getId() == $me->getId())
                // The user wants themselves as a recipient - ignore that since
                // we are going to add the user in the end either way
                continue;

            if (!$recipient->isValid()) {
                $error = ($listingUsernames) // Note that $rid has been escaped by Symfony
                       ? "There is no player called $rid"
                       : "One of the recipients you specified does not exist";
                $form->get('Recipients')->addError(new FormError($error));
                continue;
            }

            $recipientIds[] = $recipient->getId();
        }

        if (count($recipientIds) < 1)
            $form->get('Recipients')->addError(new FormError("You can't send a message to yourself!"));

        // Add the currently logged-in user to the list of recipients
        $recipientIds[] = $me->getId();

        return $recipientIds;
    }

    private function getErrorMessage(Form &$form)
    {
        foreach ($form->all() as $child)
            foreach ($child->getErrors() as $error)

                return $child->getName() . ": " . $error->getMessage();

        return "Unknown error";
    }
}
