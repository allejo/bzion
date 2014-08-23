<?php
namespace BZIon\Form\Type;

use BZIon\Form\Transformer\ModelTransformer;
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
     * Whether to include an empty element in the list
     * @var boolean
     */
    private $emptyElem;

    /**
     * A function to apply on the QueryBuilder
     * @var callable|null
     */
    private $modifier;

    /**
     * Get a new ModelType
     * @param string  $type      The type of the model
     * @param boolean $emptyElem Whether to include an empty element in the list
     * @param callable|null $modifier A function which modifies the query builder
     *                                used to fetch the Models
     */
    public function __construct($type, $emptyElem=true, $modifier=null)
    {
        $this->type = "$type";
        $this->emptyElem = $emptyElem;
        $this->modifier = $modifier;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ModelTransformer($this->type);
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $type = $this->getTypeForHumans();

        $emptyElement = ($this->emptyElem) ? array( null => '' ) : array();
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
        $type     = $this->type;
        $query    = $type::getQueryBuilder()->active();
        $modifier = $this->modifier;

        if ($modifier) {
            $query = $modifier($query);
        }

        return $query->getNames();
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
