<?php
namespace BZIon\Form\Type;

use BZIon\Form\Transformer\MultipleModelTransformer;
use BZIon\Form\Transformer\SingleModelTransformer;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @param string        $type      The type of the model
     * @param boolean       $emptyElem Whether to include an empty element in the list
     * @param callable|null $modifier  A function which modifies the query builder
     *                                 used to fetch the Models
     */
    public function __construct($type, $emptyElem = true, $modifier = null)
    {
        $this->type = "$type";
        $this->emptyElem = $emptyElem;
        $this->modifier = $modifier;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $transformer = new MultipleModelTransformer($this->type);
        } else {
            $transformer = new SingleModelTransformer($this->type);
        }

        $builder->addModelTransformer($transformer);
    }

    /**
     * Render a list of comma-separated usernames for the user to see
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($this->type === 'Role') {
            foreach ($view->vars['choices'] as $choice) {
                $role = new \Role($choice->value);
                $icon = $role->getDisplayIcon();
                if ($icon !== null) {
                    $choice->attr['data-icon'] = $icon;
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $type = $this->getTypeForHumans();

        $emptyElement = ($this->emptyElem) ? array( null => '' ) : array();
        $names = $emptyElement + $this->getAll();

        $resolver->setDefaults(array(
            'attr' => array(
                'class'            => "$type-select",
                'data-placeholder' => "Select a $type..."
            ),
            'choices' => $names,
        ));
    }

    private function getAll()
    {
        $type     = $this->type;
        $query    = \Controller::getQueryBuilder($this->type);
        $modifier = $this->modifier;

        if ($modifier) {
            $query = $modifier($query);
        }

        return $query->getNames();
    }

    private function getTypeForHumans()
    {
        $type = $this->type;

        return Inflector::tableize(Inflector::classify($type::getTypeForHumans()));
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
