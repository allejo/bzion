<?php
namespace BZIon\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class DatetimeWithTimezoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('time', 'datetime')
            ->add('timezone', new TimezoneType())
            ->addViewTransformer(new DatetimeWithTimezoneTransformer());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => true,
            'data_class' => null,
        ));
    }

    public function getName()
    {
        return 'datetimeWithTz';
    }
}
