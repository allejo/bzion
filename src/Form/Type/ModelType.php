<?php

namespace BZIon\Form\Type;

use BZIon\Form\Constraint\NotBlankModel;
use BZIon\Form\Transformer\MultipleModelTransformer;
use BZIon\Form\Transformer\SingleModelTransformer;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A Model type for use in Symfony's forms
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
     * @var bool
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
     * @param bool       $emptyElem Whether to include an empty element in the list
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
                $role = \Role::get($choice->value);
                $icon = $role->getDisplayIcon();
                if ($icon !== null) {
                    $choice->attr['data-icon'] = $icon;
                }
            }
        } elseif ($this->type === 'Country') {
            foreach ($view->vars['choices'] as $choice) {
                $country = \Country::get($choice->value);
                $choice->attr['data-iso'] = $country->getISO();
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $type = $this->getTypeForHumans();

        $emptyElement = ($this->emptyElem) ? array(null => '') : array();
        $names = $emptyElement + $this->getAll();

        $resolver->setDefaults(array(
            'attr' => array(
                'class'            => "js-select__$type",
                'data-placeholder' => "Select a $type..."
            ),
            'choices' => $names,
        ));

        $resolver->setNormalizer('constraints', function ($options, $value) {
            // One constraint should always be converted to an array
            $value = is_object($value) ? array($value) : (array) $value;

            foreach ($value as &$constraint) {
                // Symfony's default NotBlank constraint won't recognise that an
                // invalid bzion model is equivalent to a blank value. We replace
                // Symfony's validator with our own, so that an error is shown
                // when the user has selected an empty value from the list and
                // a NotBlank constraint has been provided.
                if (get_class($constraint) === 'Symfony\Component\Validator\Constraints\NotBlank') {
                    $constraint = new NotBlankModel(array(
                        'message' => $constraint->message
                    ));
                }
            }

            return $value;
        });
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
