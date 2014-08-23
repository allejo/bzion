<?php
namespace BZIon\Form\Type;

use BZIon\Form\Transformer\DatetimeWithTimezoneTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class DatetimeWithTimezoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('time', 'datetime')
            ->add(
                $builder->create('timezone', new TimezoneType())
                        ->setDataLocked(false)
            )
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
