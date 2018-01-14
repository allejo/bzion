<?php

namespace BZIon\Form\Creator;

use BZIon\Form\Constraint\NotBlankModel;
use BZIon\Form\Type\AdvancedModelType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TeamRestorationFormCreator extends ModelFormCreator
{
    /**
     * @var \Team
     */
    protected $editing;

    /**
     * @param FormBuilderInterface $builder
     *
     * @return FormBuilderInterface
     */
    protected function build($builder)
    {
        $playerModel = new AdvancedModelType('player');

        $builder->add('leader', $playerModel, [
            'constraints' => new NotBlankModel(),
            'required' => true,
        ]);

        $builder
            ->add('cancel', ButtonType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Restore Team',
                'attr' => [
                    'class' => 'c-button--blue pattern pattern--upward-stripes'
                ]
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'checkAvailableLeader'])
        ;

        return $builder;
    }

    public function checkAvailableLeader(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form->has('leader')) {
            /** @var \Player $proposedLeader */
            $proposedLeader = $form->get('leader')->getData();

            if ($proposedLeader === null) {
                return;
            }

            if ($proposedLeader->getTeam()->isValid()) {
                $form->addError(new FormError("{$proposedLeader->getName()} already belongs to a team; the leader must be a teamless player."));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $builder = \Service::getFormFactory()->createNamedBuilder(
            'TeamRestoration',
            'form',
            null,
            []
        );

        $form = $this->build($builder)->getForm();

        return $form;
    }

    public function enter($form)
    {
        $this->editing->restore();

        if ($form->has('leader')) {
            $newLeader = $form->get('leader')->getData();

            $this->editing->addMember($newLeader->getId());
            $this->editing->setLeader($newLeader->getId());
        }

        return $this->editing;
    }
}
