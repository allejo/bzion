<?php

use Symfony\Component\HttpFoundation\Request;

class MessageController extends JSONController
{
    /**
    * @todo Show an error
    */
    public function setup()
    {
        $this->requireLogin();

        if (!$this->isJson()) {
            $groups = Group::getGroups($this->getRequest()->getSession()->get("playerId"));
            Service::getTemplateEngine()->addGlobal("groups", $groups);
        }
    }

    public function composeAction()
    {
        return array("players" => Player::getPlayers());
    }

    public function showAction(Group $discussion, Player $me, Request $request)
    {
        $this->assertCanParticipate($me, $discussion);

        // Create the form to send a message to the discussion
        $form = Service::getFormFactory()->createBuilder()
            ->setAction($discussion->getUrl()) // Prevents JS from going crazy
                                               // if we load a page with AJAX
            ->add('message', 'textarea')
            ->add('Send', 'submit')
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
     * @throws HTTPException Exception thrown if the user doesn't have the
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

        $this->getRequest()->getSession()->getFlashBag()->add('success',
            "Your message was sent successfully");

        // Reset the form
        $form = $cloned;
    }
}
