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
 */
class NewsFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('category', new ModelType('NewsCategory'), array(
                'constraints' => new NotBlank()
            ))
            ->add('subject', TextType::class, array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 100,
                    )),
                ),
            ))
            ->add('content', TextareaType::class, array(
                'constraints' => new NotBlank()
            ))
            ->add('status', ChoiceType::class, array(
                'choices' => array(
                    'published' => 'Public',
                    'revision'  => 'Revision',
                    'draft'     => 'Draft',
                ),
            ))
            ->add('enter', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function fill($form, $article)
    {
        $form->get('category')->setData($article->getCategory());
        $form->get('subject')->setData($article->getSubject());
        $form->get('content')->setData($article->getContent());
        $form->get('status')->setData($article->getStatus());
    }

    /**
     * {@inheritdoc}
     */
    public function update($form, $article)
    {
        $article->updateCategory($form->get('category')->getData()->getId())
                ->updateSubject($form->get('subject')->getData())
                ->updateContent($form->get('content')->getData())
                ->updateStatus($form->get('status')->getData())
                ->updateLastEditor($this->me->getId())
                ->updateEditTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        return \News::addNews(
            $form->get('subject')->getData(),
            $form->get('content')->getData(),
            $this->me->getId(),
            $form->get('category')->getData()->getId(),
            $form->get('status')->getData()
        );
    }
}
