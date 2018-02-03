<?php
/**
 * This file contains a form creator for News articles
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for news
 *
 * @property \News|null $editing
 */
class NewsFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        $builder
            ->add('category', new ModelType('NewsCategory'), array(
                'constraints' => new NotBlank()
            ))
            ->add('subject', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, array(
                'constraints' => new NotBlank()
            ))
            ->add('publish', SubmitType::class, [
                'attr' => [
                    'class' => 'c-button--blue pattern pattern--downward-stripes',
                ],
                'label' => 'Publish',
            ])
        ;

        if ($this->editing === null || $this->editing->isDraft()) {
            $builder
                ->add('save_draft', SubmitType::class, [
                    'attr' => [
                        'class' => 'c-button--green pattern pattern--upward-stripes',
                    ],
                    'label' => 'Save Draft'
                ])
            ;
        }

        return $builder;
    }

    /**
     * {@inheritdoc}
     *
     * @param \News $article
     */
    public function fill($form, $article)
    {
        $form->get('category')->setData($article->getCategory());
        $form->get('subject')->setData($article->getSubject());
        $form->get('content')->setData($article->getContent());
    }

    /**
     * {@inheritdoc}
     *
     * @param \News $article
     */
    public function update($form, $article)
    {
        $saveDraft = $form->get('save_draft');

        $article
            ->updateCategory($form->get('category')->getData()->getId())
            ->updateSubject($form->get('subject')->getData())
            ->updateContent($form->get('content')->getData())
            ->setDraft($saveDraft && $saveDraft->isClicked())
            ->updateLastEditor($this->me->getId())
            ->updateEditTimestamp()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        $saveDraft = $form->get('save_draft');

        return \News::addNews(
            $form->get('subject')->getData(),
            $form->get('content')->getData(),
            $this->me->getId(),
            $form->get('category')->getData()->getId(),
            ($saveDraft && $saveDraft->isClicked())
        );
    }
}
