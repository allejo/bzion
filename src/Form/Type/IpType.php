<?php
namespace BZIon\Form\Type;

use BZIon\Form\Transformer\IpTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

class IpType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new IpTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            // Documentation IP addresses
            // See http://en.wikipedia.org/wiki/Reserved_IP_addresses
            'placeholder' => '192.0.2.193, 203.0.113.18, ...',
        ));
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
