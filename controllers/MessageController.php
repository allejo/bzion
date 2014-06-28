<?php

use BZIon\Form\PlayerType;
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
    }

    protected function prepareTwig()
    {
        $groups = Group::getGroups($this->getRequest()->getSession()->get("playerId"));
        Service::getTemplateEngine()->addGlobal("groups", $groups);
    }

    public function composeAction(Player $me, Request $request)
    {
        if (!$me->hasPermission(Permission::SEND_PRIVATE_MSG))
            throw new ForbiddenException("You are not allowed to send messages");

        $form = $this->createComposeForm($me);
        $form->handleRequest($request);

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
                $group_to->sendMessage($me, $content);

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

        $messages = Message::getQueryBuilder()->active()
                  ->where('group')->is($discussion)
                  ->sortBy('time')->reverse()
                  ->limit(10)->fromPage($request->query->get('page', 1))
                  ->startAt($request->query->get('end'))
                  ->endAt($request->query->get('start'))
                  ->getModels();

        $params = array(
            "form"       => $form->createView(),
            "inviteForm" => $inviteForm->createView(),
            "group"      => $discussion,
            "messages"   => $messages,
        );

        if ($request->query->has('nolayout')) {
            // Don't show the layout so that ajax can just load the messages
            return $this->render('Message/messages.html.twig', $params);
        } else {
            return $params;
        }
    }

    private function showInviteForm($discussion, $me)
    {
        $form = Service::getFormFactory()->createNamedBuilder('invite_form')
            ->add('players', new PlayerType(), array(
                'constraints' => new NotBlank(),
                'multiple' => true,
            ))
            ->add('Invite', 'submit')
            ->setAction($discussion->getUrl())->getForm();

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            foreach($form->get('players')->getData() as $player) {
                if ($discussion->isMember($player->getId()))
                    break;

                $discussion->addMember($player->getId());
            }
        }

        $this->getFlashBag()->add('success', "The conversation has been updated");

        return $form;
    }

    private function showMessageForm($discussion, $me)
    {
        // Create the form to send a message to the discussion
        $form = Service::getFormFactory()->createBuilder()
            ->add('message', 'textarea', array( 'constraints' => new NotBlank(array("message" => "You can't send an empty message!")) ))
            ->add('Send', 'submit')
            ->setAction($discussion->getUrl())
            ->getForm();

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

        $to->sendMessage($from, $message);

        $this->getFlashBag()->add('success', "Your message was sent successfully");

        // Reset the form
        $form = $cloned;
    }

    /**
     * Creates the new message form
     * @param  Player $me The currently logged-in player
     * @return Form
     */
    private function createComposeForm(Player $me)
    {
        $notBlank = array( 'constraints' => new NotBlank() );

        return Service::getFormFactory()->createBuilder()
            ->add('Recipients', new PlayerType(), array(
                'constraints' => new NotBlank(),
                'multiple' => true,
                'include' => $me,
            ))
            ->add('Subject', 'text', $notBlank)
            ->add('Message', 'textarea', $notBlank)
            ->add('Send', 'submit')
            // Prevents JS from going crazy if we load a page with AJAX
            ->setAction(Service::getGenerator()->generate('message_list'))
            ->setMethod('POST')
            ->getForm();
    }

    private function getErrorMessage(Form &$form)
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
}
