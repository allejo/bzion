<?php

use Symfony\Component\HttpFoundation\Request;

class MessageController extends HTMLController
{
    /**
    * @todo Show an error
    */
    public function setup()
    {
        $this->requireLogin();
        $groups = Group::getGroups($this->getRequest()->getSession()->get("playerId"));

        Service::getTemplateEngine()->addGlobal("groups", $groups);
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
            ->add('message', 'textarea')
            ->add('Send', 'submit')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            // The player wants to send a message
            $content = $form->get('message')->getData();
            Message::sendMessage($discussion->getId(), $me->getId(), $content);
            $request->getSession()->getFlashBag()->add('success',
                "Your message was sent successfully");
        }

        $messages = Message::getMessages($discussion->getId());

        return array("form" => $form->createView(), "group" => $discussion, "messages" => $messages);
    }

    /*
     * Make sure that a player can participate in a group
     *
     * Throws an exception if a player is not an admin or a member of that group
     * @todo Permission for spying on other people's groups?
     * @throws HTTPException
     * @param  Player        $player  The player to test
     * @param  Group          $group   The message group
     * @param  string        $message The error message to show
     * @return void
     */
    private function assertCanParticipate(Player &$player, Group &$group,
        $message="You are not allowed to participate in that discussion"
    ) {
        if (!$group->isMember($player->getId()))
            throw new ForbiddenException($message);
    }

    private function sendMessage(Player &$from, Group &$to, string $message)
    {

    }
}
