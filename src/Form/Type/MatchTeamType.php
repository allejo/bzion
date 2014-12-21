<?php
namespace BZIon\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class MatchTeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('team', new ModelType('Team'))
            ->add('score', 'integer', array(
                'constraints' => array(
                    new NotBlank(),
                    new GreaterThanOrEqual(0)
                )
            ))
            ->add('participants', new PlayerType(), array(
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
        $form = $event->getForm()->get('participants')->get('players');
        $team = $event->getForm()->get('team')->getData();

        if (!$team || !$team->isValid()) {
            return;
        }

        $players = $form->getParent()->getData();

        if (!is_array($players)) {
            $players = array($players);
        }

        foreach ($players as $player) {
            if ($player && !$team->isMember($player->getId())) {
                $message = "{$player->getUsername()} is not a member of {$team->getName()}";
                $form->addError(new FormError($message));
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'class' => 'match-team',
            ),
            'compound' => true,
        ));
    }

    public function getName()
    {
        return 'matchTeam';
    }
}
