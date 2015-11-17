<?php

namespace BZIon\Form\Type;

use BZIon\Form\Constraint\IpAddress;
use BZIon\Form\Transformer\IpTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new IpTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $addTypeConstraint = function ($options, $value) {
            // Constraint should always be converted to an array
            $value = is_object($value) ? array($value) : (array) $value;

            $value[] = new IpAddress();

            return $value;
        };

        $resolver->setDefaults(array(
            // Documentation IP address
            // See http://en.wikipedia.org/wiki/Reserved_IP_addresses
            'placeholder' => '192.0.2.193, *.example.com, ...',
        ));

        $resolver->setNormalizer('constraints', $addTypeConstraint);
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'ip_addresses';
    }
}
