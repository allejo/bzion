<?php

class MessageController extends HTMLController
{
    /**
     * List of all message groups the player is a member of
     * @var Group[]
     */
    private $groups;

    /**
    * @todo Show an error
    */
    public function setup()
    {
        $session = $this->getRequest()->getSession();

        if (!$session->has("username")) {
            Header::go("home");
        }

        $this->groups = Group::getGroups($session->get("playerId"));
    }

    public function composeAction()
    {
        return array("groups" => $this->groups, "players" => Player::getPlayers());
    }

    public function showAction(Group $discussion)
    {
        $messages = Message::getMessages($discussion->getId());

        return array("groups" => $this->groups, "group" => $discussion, "messages" => $messages);
    }
}
