<?php

namespace BZIon\Form\Type;

use BZIon\Form\Transformer\MatchTeamTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class MatchTeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                $builder
                    ->create('team', ChoiceType::class, [
                        'choices' => [
                            'red' => 'Red Team',
                            'green' => 'Green Team',
                            'blue' => 'Blue Team',
                            'purple' => 'Purple Team',
                             null => '',
                        ] + \Controller::getQueryBuilder('Team')->getNames(),
                        'constraints' => new NotBlank(),
                        'disabled'    => $options['disableTeam']
                    ])
                    ->addModelTransformer(new MatchTeamTransformer())
            )
            ->add('score', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0)
                ],
            ])
            ->add('participants', new AdvancedModelType('player'), [
                'constraints' => [
                    new Count([
                        'min' => 2,
                    ]),
                ],
                'multiple' => true,
                'required' => true,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'checkTeamMembers'])
        ;
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

        if (!$team || !$team instanceof \Model || !$team->isValid()) {
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
