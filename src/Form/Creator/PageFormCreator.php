<?php
/**
 * This file contains a form creator for Pages
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Form creator for pages
 */
class PageFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 32,
                    )),
                ),
            ))
            ->add('content', 'textarea', array(
                'constraints' => new NotBlank()
            ))
            ->add('status', 'choice', array(
                'choices' => array(
                    'live' => 'Public',
                    'revision' => 'Revision',
                    'disabled' => 'Disabled',
                ),
            ))
            ->add('enter', 'submit');
    }

    /**
     * {@inheritDoc}
     */
    public function fill($form, $page)
    {
        $form->get('name')->setData($page->getName());
        $form->get('content')->setData($page->getContent());
        $form->get('status')->setData($page->getStatus());
    }
}
