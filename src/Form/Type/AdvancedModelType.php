<?php
namespace BZIon\Form\Type;

use BZIon\Form\Constraint\ValidModel;
use BZIon\Form\Transformer\MultipleAdvancedModelTransformer;
use BZIon\Form\Transformer\SingleAdvancedModelTransformer;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class AdvancedModelType extends AbstractType
{
    /**
     * The types of the model
     */
    private $types = array();

    /**
     * Whether the user gave us names instead of IDs
     * @var boolean
     */
    private $listNames = false;

    /**
     * A player to always include
     * @var Player|null
     */
    private $include = null;

    /**
     * Whether more than 1 players can be provided
     * @var boolean
     */
    private $multiple = false;

    /**
     * Create new ModelType
     * @param string|string[] $type The types of the model
     */
    public function __construct($type)
    {
        $this->types = (is_array($type)) ? $type : array($type);
        $this->types = array_map('strtolower', $this->types);
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['include'])) {
            $this->include = $options['include'];
        }

        if (isset($options['multiple'])) {
            $this->multiple = $options['multiple'];
        }

        if ($this->include && !$this->multiple) {
            throw new \LogicException(
                "You can't include an object in a single selection!"
            );
        }

        $builderName = ucfirst($builder->getName());

        // TODO: Use a more accurate placeholder
        $placeholder = ($this->multiple) ? 'brad, kierra, ...' : null;

        // Model IDs that will be manipulated by javascript
        $builder->add('ids', 'hidden', array(
            'attr' => array(
                'class' => 'select2-compatible',
                'data-label' => $builderName,
                'data-multiple' => $this->multiple,
                'data-required' => $options['required']
            ),
        ));

        // Model name inputs that will be edited by users if javascript is
        // disabled
        foreach ($this->types as $type) {
            $pluralType = ($this->multiple) ? Inflector::pluralize($type) : $type;
            $label = (count($this->types) > 1) ? "$builderName $pluralType" : $builderName;

            $builder->add(
                $builder->create($type, 'text', array(
                    'attr' => array(
                        'class'       => 'model-select',
                        'data-type'   => $type,
                        'placeholder' => $placeholder,
                    ),
                    'label'    => $label,
                    'required' => false
                ))
            );
        }

        if ($this->multiple) {
            $transformer = new MultipleAdvancedModelTransformer($this->types);
            if ($this->include) {
                $transformer->addInclude($this->include);
            }
        } else {
            $transformer = new SingleAdvancedModelTransformer($this->types);
        }

        $builder->addViewTransformer($transformer);

        // Make sure we can change the values provided by the user
        $builder->setDataLocked(false);
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // Make sure that the view values are set properly, so that, for
        // example, the JSON input is updated to the data from the plain-text
        // input (allowing the client to switch JS on and off arbitrarily)
        foreach($view->children as &$child) {
            $name = $child->vars['name'];

            // TODO: Show the old value to the user when needed to correct
            // errors
            if (isset($form->getViewData()[$name])) {
                $child->vars['value'] = $form->getViewData()[$name];
            }
        }

        $view->vars['attr']['data-multiple'] = ($this->multiple) ? '1' : '0';
        $view->vars['attr']['data-types'] = implode(',', $this->types);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $addValidModelConstraint = function($options, $value) {
            // A constraint should always be converted to an array
            $value = is_object($value) ? array($value) : (array) $value;

            $value[] = new ValidModel(array(
                'single' => !$options['multiple']
            ));

            return $value;
        };

        $resolver->setDefined(array('include'));
        $resolver->setDefaults(array(
            'compound' => true,
            'data_class' => null,
            'error_bubbling' => false,
            'label'    => false,
            'multiple' => false,
        ));

        $resolver->setNormalizer('constraints', $addValidModelConstraint);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'advanced_model';
    }
}