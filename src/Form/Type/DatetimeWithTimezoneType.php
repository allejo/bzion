<?php

namespace BZIon\Form\Type;

use BZIon\Form\Transformer\DatetimeWithTimezoneTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatetimeWithTimezoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('time', DateTimeType::class, array(
                'with_seconds' => $options['with_seconds']
            ))
            ->add(
                $builder->create('timezone', TimezoneType::class)
                        ->setDataLocked(false)
            )
            ->addViewTransformer(new DatetimeWithTimezoneTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound'     => true,
            'data_class'   => null,
            'with_seconds' => false
        ));
    }

    public function getName()
    {
        return 'datetimeWithTz';
    }
}
