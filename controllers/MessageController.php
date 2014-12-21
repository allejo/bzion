<?php

use BZIon\Event\Events;
use BZIon\Event\GroupAbandonEvent;
use BZIon\Event\GroupJoinEvent;
use BZIon\Event\GroupKickEvent;
use BZIon\Event\GroupRenameEvent;
use BZIon\Event\NewMessageEvent;
use BZIon\Form\Creator\GroupFormCreator;
use BZIon\Form\Creator\GroupInviteFormCreator;
use BZIon\Form\Creator\GroupRenameFormCreator;
use BZIon\Form\Creator\MessageFormCreator;
use BZIon\Form\Creator\MessageSearchFormCreator;
use BZIon\Search\MessageSearch;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends JSONController
{
    public function setup()
    {
        $this->requireLogin();
    }

    protected function prepareTwig()
    {
        $groups = Group::getGroups($this->getMe()->getId());
        Service::getTemplateEngine()->addGlobal("groups", $groups);

        $creator = new MessageSearchFormCreator();
        $searchForm = $creator->create();
        Service::getTemplateEngine()->addGlobal("searchForm", $searchForm->createView());

    }

    public function composeAction(Player $me, Request $request)
    {
        if (!$me->hasPermission(Permission::SEND_PRIVATE_MSG))
            throw new ForbiddenException("You are not allowed to send messages");

        $creator = new GroupFormCreator($me);
        $form = $creator->create()->handleRequest($request);

        if ($form->isSubmitted()) {
            if (count($form->get('Recipients')->getData()) < 2) {
                $form->get('Recipients')->addError(new FormError("You can't send a message to yourself!"));
            }
            if ($form->isValid()) {
                $subject = $form->get('Subject')->getData();
                $content = $form->get('Message')->getData();
                $recipientIds = array();

                foreach ($form->get('Recipients')->getData() as $player) {
                    $recipientIds[] = $player->getId();
                }

                $group_to = Group::createGroup($subject, $me->getId(), $recipientIds);
                $message = $group_to->sendMessage($me, $content);

                $event = new NewMessageEvent($message, true);
                $this->dispatch(Events::MESSAGE_NEW, $event);

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

        return array("form" => $form->createView());
    }

    public function showAction(Group $discussion, Player $me, Request $request)
    {
        $this->assertCanParticipate($me, $discussion);
        $discussion->markReadBy($me->getId());

        $form = $this->showMessageForm($discussion, $me);
        $inviteForm = $this->showInviteForm($discussion, $me);
        $renameForm = $this->showRenameForm($discussion, $me);

        $messages = $this->getQueryBuilder()
                  ->where('group')->is($discussion)
                  ->sortBy('time')->reverse()
                  ->limit(10)->fromPage($request->query->get('page', 1))
                  ->startAt($request->query->get('end'))
                  ->endAt($request->query->get('start'))
                  ->getAllEvents();

        $params = array(
            "form"       => $form->createView(),
            "inviteForm" => $inviteForm->createView(),
            "renameForm" => $renameForm->createView(),
            "group"      => $discussion,
            "messages"   => $messages['messages'],
            "events"     => $messages['events']
        );

        if ($request->query->has('nolayout')) {
            // Don't show the layout so that ajax can just load the messages
            return $this->render('Message/messages.html.twig', $params);
        } else {
            return $params;
        }
    }

    public function leaveAction(Player $me, Group $discussion)
    {
        if (!$discussion->isMember($me->getId())) {
            throw new ForbiddenException("You are not a member of this discussion.");
        } elseif ($discussion->getCreator()->getId() == $me->getId()) {
            throw new ForbiddenException("You can't abandon the conversation you started!");
        }

        return $this->showConfirmationForm(function () use ($discussion, $me) {
            $discussion->removeMember($me->getId());

            $event = new GroupAbandonEvent($discussion, $me);
            Service::getDispatcher()->dispatch(Events::GROUP_ABANDON, $event);

            return new RedirectResponse(Service::getGenerator()->generate('message_list'));
        },  "Are you sure you want to abandon this discussion?",
            "You will no longer receive messages from this conversation", "Leave");
    }

    public function kickAction(Group $discussion, Player $player, Player $me)
    {
        $this->assertCanEdit($me, $discussion, "You are not allowed to kick a player off that discussion!");

        if ($discussion->isCreator($player->getId()))
            throw new ForbiddenException("You can't leave your own discussion.");

        if (!$discussion->isMember($player->getId()))
            throw new ForbiddenException("The specified player is not a member of this conversation.");

        return $this->showConfirmationForm(function () use ($discussion, $player, $me) {
            $discussion->removeMember($player->getId());

            $event = new GroupKickEvent($discussion, $player, $me);
            Service::getDispatcher()->dispatch(Events::GROUP_KICK, $event);

            return new RedirectResponse($discussion->getUrl());
        },  "Are you sure you want to kick {$player->getEscapedUsername()} from the discussion?",
            "Player {$player->getUsername()} has been kicked from the conversation", "Kick");
    }

    public function searchAction(Player $me, Request $request)
    {
        $query = $request->query->get('q');

        if (strlen($query) < 3 && !$this->isDebug()) {
            // TODO: Find a better error message
            throw new BadRequestException('The search term you have provided is too short');
        }

        $search  = new MessageSearch($this->getQueryBuilder(), $me);
        $results = $search->search($query);

        return array(
            'messages' => $results
        );
    }

    /**
     * @param Group $discussion
     * @param Player $me
     */
    private function showInviteForm($discussion, $me)
    {
        $creator = new GroupInviteFormCreator($discussion);
        $form = $creator->create()->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $this->assertCanEdit($me, $discussion);
            $invitees = array();

            foreach ($form->get('players')->getData() as $player) {
                if (!$discussion->isMember($player->getId())) {
                    $discussion->addMember($player->getId());
                    $invitees[] = $player;
                }
            }

            if (!empty($invitees)) {
                $event = new GroupJoinEvent($discussion, $invitees);
                Service::getDispatcher()->dispatch(Events::GROUP_JOIN, $event);
            }

            $this->getFlashBag()->add('success', "The conversation has been updated");

            // Reset the form fields
            return $creator->create();
        }

        return $form;
    }

    /**
     * @param Group $discussion
     * @param Player $me
     */
    private function showRenameForm($discussion, $me)
    {
        $creator = new GroupRenameFormCreator($discussion);
        $form = $creator->create()->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $this->assertCanEdit($me, $discussion);

            $newName = $form->get('subject')->getData();

            $event = new GroupRenameEvent($discussion, $discussion->getSubject(), $newName, $me);
            $discussion->setSubject($newName);
            Service::getDispatcher()->dispatch(Events::GROUP_RENAME, $event);

            $this->getFlashBag()->add('success', "The conversation has been updated");
        }

        return $form;
    }

    /**
     * @param Group $discussion
     * @param Player $me
     */
    private function showMessageForm($discussion, $me)
    {
        // Create the form to send a message to the discussion
        $creator = new MessageFormCreator($discussion);
        $form = $creator->create();

        // Keep a cloned version so we can come back to it later, if we need
        // to reset the fields of the form
        $cloned = clone $form;
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            // The player wants to send a message
            $this->sendMessage($me, $discussion, $form, $cloned);
        } elseif ($form->isSubmitted() && $this->isJson())
            throw new BadRequestException($this->getErrorMessage($form));

        return $form;
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
    private function assertCanParticipate(Player $player, Group $group,
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
     * @param Form $cloned
     * @return void
     */
    private function sendMessage(Player $from, Group $to, &$form, $cloned)
    {
        if (!$from->hasPermission(Permission::SEND_PRIVATE_MSG))
            throw new ForbiddenException("You are not allowed to send messages");

        $message = $form->get('message')->getData();
        $message = $to->sendMessage($from, $message);

        $this->getFlashBag()->add('success', "Your message was sent successfully");

        // Let javascript know the message's ID
        $this->attributes->set('id', $message->getId());

        // Reset the form
        $form = $cloned;

        // Notify everyone that we sent a new message
        $event = new NewMessageEvent($message, false);
        $this->dispatch(Events::MESSAGE_NEW, $event);
    }

    /**
     * @return string|null
     */
    private function getErrorMessage(Form $form)
    {
        foreach ($form->all() as $child) {
            foreach ($child->getErrors() as $error) {
                return $error->getMessage();
            }
        }

        foreach ($form->getErrors() as $error) {
            return $error->getMessage();
        }

        return "Unknown Error";
    }

    /**
     * Make sure that a player can edit a conversation
     *
     * Throws an exception if a player is not an admin or the leader of a team
     * @throws HTTPException
     * @param  Player        $player  The player to test
     * @param  Group         $group   The team
     * @param  string        $message The error message to show
     * @return void
     */
    private function assertCanEdit(Player $player, Group $group, $message="You are not allowed to edit the discussion")
    {
        if ($group->getCreator()->getId() != $player->getId())
                throw new ForbiddenException($message);
    }
}
