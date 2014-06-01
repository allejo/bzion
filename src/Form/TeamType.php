<?php
namespace BZIon\Form;

use Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ModelTransformer('Team');
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $names = array();

        $names[0] = "Select a team...";

        foreach(Team::getTeamNames() as $team)
            $names[$team['id']] = $team['name'];

        $resolver->setDefaults(array(
            'attr' => array( 'class' => 'team-select'),
            'choices' => $names,
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'team';
    }
}
