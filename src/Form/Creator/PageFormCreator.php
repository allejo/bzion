<?php
/**
 * This file contains a form creator for Pages
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for pages
 */
class PageFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add(
                $builder->create('name', 'text', array(
                    'constraints' => array(
                        new NotBlank(), new Length(array(
                            'max' => 32,
                        )),
                    ),
                    'data' => $this->controller->data->get('name')
                ))->setDataLocked(false)
            )
            ->add('content', 'textarea', array(
                'constraints' => new NotBlank()
            ))
            ->add('status', 'choice', array(
                'choices' => array(
                    'live'     => 'Public',
                    'revision' => 'Revision',
                    'disabled' => 'Disabled',
                ),
                'description' => "'Revision' pages are accessible by all users but not listed in the menu, " .
                    "while 'Disabled' pages cannot be accessed by players."
            ))
            ->add('enter', 'submit', [
                'attr' => [
                    'class' => 'c-button--blue pattern pattern--downward-stripes',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function fill($form, $page)
    {
        $form->get('name')->setData($page->getName());
        $form->get('content')->setData($page->getContent());
        $form->get('status')->setData($page->getStatus());
    }

    /**
     * {@inheritdoc}
     */
    public function update($form, $page)
    {
        $page->setName($form->get('name')->getData())
             ->setContent($form->get('content')->getData())
             ->setStatus($form->get('status')->getData())
             ->updateEditTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        return \Page::addPage(
            $form->get('name')->getData(),
            $form->get('content')->getData(),
            $this->me->getId(),
            $form->get('status')->getData()
        );
    }
}
