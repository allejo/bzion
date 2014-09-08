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
     * The user who is submitting the form
     * @var \Player
     */
    protected $me;

    /**
     * The controller showing the form
     * @var \Controller|null
     */
    protected $controller;

    /**
     * Create a new ModelFormCreator
     *
     * @param \Model|null $editing The model that's being edited
     * @param \Player|null $me The user who is submitting the form
     * @param \Controller|null $controller The controller showing the form
     */
    public function __construct($editing = null, $me = null, $controller = null)
    {
        $this->editing = $editing;
        $this->me = ($me) ?: \Player::invalid();
        $this->controller = $controller;
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
     * Update a model based on a form's data
     *
     * Override this in your form
     *
     * @param  Form $form The form to use
     * @param  \Model $model The model to update
     * @return void
     */
    public function update($form, $model)
    {
        throw new BadMethodCallException("Please override the update() method in the FormCreator class for the model");
    }

    /**
     * Enter the data of a valid form into the database
     * @param  Form $form The submitted form
     * @return \Model
     */
    public function enter($form)
    {
        throw new BadMethodCallException("Please override the enter() method in the FormCreator class for the model");
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
