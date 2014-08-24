<?php
/**
 * This file contains a form creator for Models
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

/**
 * A form creator for Models which cooperates with the CRUDController
 */
abstract class ModelFormCreator implements FormCreatorInterface
{
    /**
     * The model that the form is editing
     * @var \Model|null
     */
    protected $editing;

    /**
     * Create a new ModelForm
     * @param \Model|null $editing The model that's being edited
     */
    public function __construct($editing = null)
    {
        $this->editing = $editing;
    }

    /**
     * Build a form
     *
     * @param  FormBuilder $builder Symfony's form builder
     * @return FormBuilder
     */
    abstract protected function build($builder);

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $builder = \Service::getFormFactory()->createNamedBuilder($this->getName());
        $form = $this->build($builder)->getForm();

        if ($this->editing) {
            $this->fill($form, $this->editing);
        }

        return $form;
    }

    /**
     * Fill the form with a model's data
     *
     * Override this in your form
     *
     * @param  Form   $form  The form to fill
     * @param  \Model $model The model to provide the data to use
     * @return void
     */
    public function fill($form, $model)
    {
    }

    /**
     * Are we editing or creating a model?
     * @return boolean True if editing, false when creating a model
     */
    protected function isEdit()
    {
        return (bool) $this->editing;
    }

    /**
     * Get the name of the form
     * @return string
     */
    protected function getName()
    {
        return 'form';
    }
}
