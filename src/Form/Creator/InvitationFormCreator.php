<?php

namespace BZIon\Form\Creator;

use BZIon\Event\Events;
use BZIon\Event\TeamInviteEvent;
use BZIon\Form\Constraint\NotBlankModel;
use BZIon\Form\Type\AdvancedModelType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @property \Team $editing
 */
class InvitationFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        $invitedPlayer = new AdvancedModelType(\Player::class, [
            'constraints' => [
                new NotBlankModel(),
            ],
            'label' => 'Invitation Recipient',
            'required' => true,
        ]);

        $targetTeam = $builder
            ->create('target_team', HiddenType::class, [
                'data' => $this->editing->getId(),
            ])
            ->setDataLocked(true)
        ;

        $builder
            ->add($targetTeam)
            ->add('invited_player', $invitedPlayer)
            ->add('message', TextareaType::class, [
                'required' => false,
            ])
            ->add('cancel', ButtonType::class, [
                'label' => 'Cancel',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Invite',
                'attr' => [
                    'class' => 'c-button--blue pattern pattern--upward-stripes'
                ]
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'checkPlayerEligibility'])
        ;

        return $builder;
    }

    public function checkPlayerEligibility(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form->has('invited_player')) {
            $formElement = $form->get('invited_player');

            /** @var \Player|null $proposedInvitee */
            $proposedInvitee = $formElement->getData();

            if ($proposedInvitee === null) {
                $formElement->addError(new FormError('Invited player not found.'));
                return;
            }

            if ($this->editing->isMember($proposedInvitee->getId())) {
                $formElement->addError(new FormError('This player is already a member of that team.'));
            }

            if (\Invitation::playerHasInvitationToTeam($proposedInvitee->getId(), $this->editing->getId())) {
                $formElement->addError(new FormError('This player already has an invitation to this team.'));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        $invite = \Invitation::sendInvite(
            $form->get('invited_player')->getData(),
            $form->get('target_team')->getData(),
            $this->me->getId(),
            $form->get('message')->getData()
        );
        \Service::getDispatcher()->dispatch(Events::TEAM_INVITE, new TeamInviteEvent($invite));

        return $invite;
    }
}
