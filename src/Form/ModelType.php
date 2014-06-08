<?php
namespace BZIon\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A Model type for use in Symphony's forms
 */
class ModelType extends AbstractType
{
    /**
     * The type of the model
     * @var string
     */
    private $type;

    /**
     * Get a new ModelType
     * @param string $type The type of the model
     */
    public function __construct($type)
    {
        $this->type = "$type";
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ModelTransformer($this->type);
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $type = $this->getTypeForHumans();

        $emptyElement = array( null => '' );
        $names = $emptyElement + $this->getAll();

        $resolver->setDefaults(array(
            'attr' => array(
                'class' => "$type-select",
                'data-placeholder' => "Select a $type..."
            ),
            'choices' => $names,
        ));
    }

    private function getAll()
    {
        $type = $this->type;

        return $type::getQueryBuilder()->active()->getNames();
    }

    private function getTypeForHumans()
    {
        $type = $this->type;

        return strtolower($type::getTypeForHumans());
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return $this->getTypeForHumans();
    }
}
