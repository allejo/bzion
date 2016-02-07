<?php

use BZIon\Event\ConversationAbandonEvent;
use BZIon\Event\ConversationJoinEvent;
use BZIon\Event\ConversationKickEvent;
use BZIon\Event\ConversationRenameEvent;
use BZIon\Event\Events;
use BZIon\Event\NewMessageEvent;
use BZIon\Form\Creator\ConversationFormCreator;
use BZIon\Form\Creator\ConversationInviteFormCreator;
use BZIon\Form\Creator\ConversationRenameFormCreator;
use BZIon\Form\Creator\MessageFormCreator;
use BZIon\Form\Creator\MessageSearchFormCreator;
use BZIon\Search\MessageSearch;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends JSONController
{
    public function setup()
    {
        $this->requireLogin();
    }

    protected function prepareTwig()
    {
        $currentPage = $this->getRequest()->query->get('page', 1);

        $query = $this->getQueryBuilder('Conversation')
            ->forPlayer($this->getMe())
            ->sortBy('last_activity')->reverse()
            ->limit(5)->fromPage($currentPage);

        $creator = new MessageSearchFormCreator();
        $searchForm = $creator->create();

        $twig = $this->container->get('twig');
        $twig->addGlobal("conversations", $query->getModels());
        $twig->addGlobal("currentPage", $currentPage);
        $twig->addGlobal("totalPages", $query->countPages());
        $twig->addGlobal("searchForm", $searchForm->createView());

        return $twig;
    }

    public function listAction()
    {
        return array();
    }

    public function composeAction(Player $me, Request $request)
    {
        if (!$me->hasPermission(Permission::SEND_PRIVATE_MSG)) {
            throw new ForbiddenException("You are not allowed to send messages");
        }

        $creator = new ConversationFormCreator($me);
        $form = $creator->create()->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $subject = $form->get('Subject')->getData();
                $content = $form->get('Message')->getData();
                $recipients = $form->get('Recipients')->getData();

                $conversation_to = Conversation::createConversation($subject, $me->getId(), $recipients);
                $message = $conversation_to->sendMessage($me, $content);

                $event = new NewMessageEvent($message, true);
                $this->dispatch(Events::MESSAGE_NEW, $event);

                if ($this->isJson()) {
                    return new JsonResponse(array(
                        'success' => true,
                        'message' => 'Your message was sent successfully',
                        'id'      => $conversation_to->getId()
                    ));
                } else {
                    return new RedirectResponse($conversation_to->getUrl());
                }
            } elseif ($this->isJson()) {
                throw new BadRequestException($this->getErrorMessage($form));
            }
        } else {
            // Load the list of recipients from the URL
            if ($request->query->has('recipients')) {
                $form->get('Recipients')->setData($this->decompose(
                    $request->query->get('recipients'),
                    array('Player', 'Team')
                ));
            }
        }

        return array("form" => $form->createView());
    }

    public function showAction(Conversation $conversation, Player $me, Request $request)
    {
        $this->assertCanParticipate($me, $conversation);
        $conversation->markReadBy($me->getId());

        $form = $this->showMessageForm($conversation, $me);
        $inviteForm = $this->showInviteForm($conversation, $me);
        $renameForm = $this->showRenameForm($conversation, $me);

        $messages = $this->getQueryBuilder('AbstractMessage')
                  ->where('conversation')->is($conversation)
                  ->sortBy('time')->reverse()
                  ->limit(10)->fromPage($request->query->get('page', 1))
                  ->startAt($request->query->get('end'))
                  ->endAt($request->query->get('start'))
                  ->getModels();

        // Hide the details (author, timestamp) of the first message if they're
        // already shown in the previous message (useful for AJAX calls)
        if($request->query->getBoolean('hideFirstDetails')) {
            $previousMessage = Message::get($request->query->get('end'));
        } else {
            $previousMessage = null;
        }

        $params = array(
            "form"         => $form->createView(),
            "inviteForm"   => $inviteForm->createView(),
            "renameForm"   => $renameForm->createView(),
            "conversation" => $conversation,
            "messages"     => $messages,
            "previousMessage" => $previousMessage
        );

        if ($request->query->has('nolayout')) {
            if ($request->query->getBoolean('reviewLastDetails')) {
                // An AJAX call has asked us to check if details (author,
                // timestamp) will need to be shown for the next message

                $nextMessage = Message::get($request->query->get('start'));
                $lastMessage = reset($messages);

                if ($lastMessage !== false
                    && $lastMessage->isMessage()
                    && $lastMessage->getTimestamp()->isSameDay($nextMessage->getTimestamp())
                    && $lastMessage->getAuthor()->isSameAs($nextMessage->getAuthor())
                ) {
                    $hideLastDetails = true;
                } else {
                    $hideLastDetails = false;
                }

                // Add $hideLastDetails to the list of JSON parameters
                $this->attributes->set('hideLastDetails', $hideLastDetails);
            }

            // Don't show the layout so that ajax can just load the messages
            return $this->render('Message/messages.html.twig', $params);
        } else {
            return $params;
        }
    }

    public function leaveAction(Player $me, Conversation $conversation)
    {
        if (!$conversation->isMember($me)) {
            throw new ForbiddenException("You are not a member of this discussion.");
        } elseif ($conversation->getCreator()->isSameAs($me)) {
            throw new ForbiddenException("You can't abandon the conversation you started!");
        }

        // TODO: Fix that later
        return $this->showConfirmationForm(function () use ($conversation, $me) {
            $conversation->removeMember($me);

            $event = new ConversationAbandonEvent($conversation, $me);
            Service::getDispatcher()->dispatch(Events::CONVERSATION_ABANDON, $event);

            return new RedirectResponse(Service::getGenerator()->generate('message_list'));
        },  "Are you sure you want to abandon this conversation?",
            "You will no longer receive messages from this conversation", "Leave");
    }

    public function kickAction(Conversation $conversation, Player $player, Player $me)
    {
        $this->assertCanEdit($me, $conversation, "You are not allowed to kick a player off that discussion!");

        if ($conversation->isCreator($player->getId())) {
            throw new ForbiddenException("You can't leave your own conversation.");
        }

        if (!$conversation->isMember($player)) {
            throw new ForbiddenException("The specified player is not a member of this conversation.");
        }

        return $this->showConfirmationForm(function () use ($conversation, $player, $me) {
            $conversation->removeMember($player);

            $event = new ConversationKickEvent($conversation, $player, $me);
            Service::getDispatcher()->dispatch(Events::CONVERSATION_KICK, $event);

            return new RedirectResponse($conversation->getUrl());
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
     * @param Conversation  $conversation
     * @param Player $me
     *
     * @return $this|Form|\Symfony\Component\Form\FormInterface
     */
    private function showInviteForm($conversation, $me)
    {
        $creator = new ConversationInviteFormCreator($conversation);
        $form = $creator->create()->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $this->assertCanEdit($me, $conversation);
            $invitees = array();

            foreach ($form->get('players')->getData() as $player) {
                if (!$conversation->isMember($player)) {
                    $conversation->addMember($player);
                    $invitees[] = $player;
                }
            }

            if (!empty($invitees)) {
                $event = new ConversationJoinEvent($conversation, $invitees);
                Service::getDispatcher()->dispatch(Events::CONVERSATION_JOIN, $event);
            }

            $this->getFlashBag()->add('success', "The conversation has been updated");

            // Reset the form fields
            return $creator->create();
        }

        return $form;
    }

    /**
     * @param Conversation  $conversation
     * @param Player $me
     */
    private function showRenameForm($conversation, $me)
    {
        $creator = new ConversationRenameFormCreator($conversation);
        $form = $creator->create()->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $this->assertCanEdit($me, $conversation);

            $newName = $form->get('subject')->getData();

            $event = new ConversationRenameEvent($conversation, $conversation->getSubject(), $newName, $me);
            $conversation->setSubject($newName);
            Service::getDispatcher()->dispatch(Events::CONVERSATION_RENAME, $event);

            $this->getFlashBag()->add('success', "The conversation has been updated");
        }

        return $form;
    }

    /**
     * @param Conversation  $conversation
     * @param Player $me
     */
    private function showMessageForm($conversation, $me)
    {
        // Create the form to send a message to the conversation
        $creator = new MessageFormCreator($conversation);
        $form = $creator->create();

        // Keep a cloned version so we can come back to it later, if we need
        // to reset the fields of the form
        $cloned = clone $form;
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            // The player wants to send a message
            $this->sendMessage($me, $conversation, $form, $cloned);
        } elseif ($form->isSubmitted() && $this->isJson()) {
            throw new BadRequestException($this->getErrorMessage($form));
        }

        return $form;
    }

    /**
     * Make sure that a player can participate in a conversation
     *
     * Throws an exception if a player is not an admin or a member of that conversation
     * @todo Permission for spying on other people's conversations?
     * @param  Player        $player  The player to test
     * @param  Conversation         $conversation   The message conversation
     * @param  string        $message The error message to show
     * @throws HTTPException
     * @return void
     */
    private function assertCanParticipate(Player $player, Conversation $conversation,
        $message = "You are not allowed to participate in that discussion"
    ) {
        if (!$conversation->isMember($player)) {
            throw new ForbiddenException($message);
        }
    }

    /**
     * Sends a message to a conversation
     *
     * @param  Player        $from   The sender
     * @param  Conversation         $to     The conversation that will receive the message
     * @param  Form          $form   The message's form
     * @param  Form          $form   The form before it handled the request
     * @param  Form          $cloned
     * @throws HTTPException Thrown if the user doesn't have the
     *                              SEND_PRIVATE_MSG permission
     * @return void
     */
    private function sendMessage(Player $from, Conversation $to, &$form, $cloned)
    {
        if (!$from->hasPermission(Permission::SEND_PRIVATE_MSG)) {
            throw new ForbiddenException("You are not allowed to send messages");
        }

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
     * @param  Player        $player  The player to test
     * @param  Conversation         $conversation   The team
     * @param  string        $message The error message to show
     * @throws HTTPException
     * @return void
     */
    private function assertCanEdit(Player $player, Conversation $conversation, $message = "You are not allowed to edit the discussion")
    {
        if ($conversation->getCreator()->getId() != $player->getId()) {
            throw new ForbiddenException($message);
        }
    }
}
