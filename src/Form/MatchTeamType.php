<?php
namespace BZIon\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class MatchTeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('team', new TeamType())
            ->add('score', 'integer')
            ->add('participants', new PlayerType(), array(
                'required' => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'class' => 'match-team',
            ),
            'inherit_data' => true,
        ));
    }

    public function getName()
    {
        return 'matchTeam';
    }
}
