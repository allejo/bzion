<?php
/**
 * This file contains a form creator for Pages
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for pages
 *
 * @property \Page $editing
 */
class PageFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        $editingDraftOrCreatingNew = ($this->editing === null || $this->editing->isDraft());
        $editingPublishedPage = ($this->editing !== null && !$this->editing->isDraft());

        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 32,
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('is_unlisted', CheckboxType::class, [
                'label' => 'Unlisted Page',
                'required' => false,
                'attr' => [
                    'data-help-message' => 'This page will not be listed on the footer of the website',
                ],
            ])
            ->add('modify_draft', SubmitType::class, [
                'attr' => [
                    'class' => 'c-button--green pattern pattern--upward-stripes',
                ],
                'label' => $editingDraftOrCreatingNew ? 'Save Draft' : 'Unpublish Page',
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'c-button--blue pattern pattern--downward-stripes',
                ],
                'label' => $editingPublishedPage ? 'Save Changes' : 'Publish Page',
            ])
        ;

        return $builder;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Page $page
     */
    public function fill($form, $page)
    {
        $form->get('name')->setData($page->getName());
        $form->get('content')->setData($page->getContent());
        $form->get('is_unlisted')->setData($page->isUnlisted());
    }

    /**
     * {@inheritdoc}
     *
     * @param \Page $page
     */
    public function update($form, $page)
    {
        $saveDraft = $form->get('modify_draft');

        $page
            ->setName($form->get('name')->getData())
            ->setContent($form->get('content')->getData())
            ->setUnlisted($form->get('is_unlisted')->getData())
            ->setDraft($saveDraft->isClicked())
            ->updateEditTimestamp()
        ;
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
            $form->get('modify_draft')->isClicked(),
            $form->get('is_unlisted')->getData()
        );
    }
}
