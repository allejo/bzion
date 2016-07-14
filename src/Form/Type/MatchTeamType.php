<?php

namespace BZIon\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class MatchTeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('team', new ModelType('Team'), array(
                'constraints' => new NotBlank(),
                'disabled'    => $options['disableTeam']
            ))
            ->add('score', IntegerType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new GreaterThanOrEqual(0)
                )
            ))
            ->add('participants', new AdvancedModelType('player'), array(
                'multiple' => true,
                'required' => false,
            ))
            ->addEventListener(FormEvents::POST_SUBMIT, array($this, 'checkTeamMembers'));
    }

    /**
     * Form event handler that makes sure the participants are actually members
     * of the specified team
     * @param  FormEvent $event
     * @return void
     */
    public function checkTeamMembers(FormEvent $event)
    {
        $players = $event->getForm()->get('participants');
        $team = $event->getForm()->get('team')->getData();

        if (!$team || !$team->isValid()) {
            return;
        }

        foreach ($players->getData() as $player) {
            if ($player && !$team->isMember($player->getId())) {
                $message = "{$player->getUsername()} is not a member of {$team->getName()}";
                $players->addError(new FormError($message));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'class' => 'match-team',
            ),
            'compound'    => true,
            'disableTeam' => false
        ));
    }

    public function getName()
    {
        return 'matchTeam';
    }
}
