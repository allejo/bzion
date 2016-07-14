<?php
/**
 * This file contains a form creator that generates confirmation forms
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Form creator for confirmation dialogs
 */
class ConfirmationFormCreator implements FormCreatorInterface
{
    /**
     * The primary action of the form (e.g. "Yes" or "Delete"), shown to the user
     * @var string
     */
    private $action;

    /**
     * The URL where the form should redirect on cancellation
     * @var string
     */
    private $originalUrl;

    /**
     * Whether to show "No" instead of cancel
     * @var bool
     */
    private $no;

    /**
     * Create a new confirmation form
     * @param string $action      The text to show on the "Yes" button
     * @param string $originalUrl The URL which the user is coming from
     * @param bool   $no          Whether to show "No" instead of cancel
     */
    public function __construct($action, $originalUrl, $no = false)
    {
        $this->action = $action;
        $this->originalUrl = $originalUrl;
        $this->no = $no;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $builder = \Service::getFormFactory()->createNamedBuilder('confirm_form');

        return $builder
            ->add('confirm', SubmitType::class, array(
                'label' => $this->action
            ))
            ->add(($this->action == 'Yes' || $this->no) ? 'No' : 'Cancel', 'submit')
            ->add('original_url', HiddenType::class, array(
                'data' => $this->originalUrl
            ))
            ->getForm();
    }
}
