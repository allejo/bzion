<?php
/**
 * This file contains a form creator for News articles
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\ModelType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Form creator for news
 */
class NewsFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('category', new ModelType('NewsCategory'))
            ->add('subject', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 100,
                    )),
                ),
            ))
            ->add('content', 'textarea', array(
                'constraints' => new NotBlank()
            ))
            ->add('status', 'choice', array(
                'choices' => array(
                    'published' => 'Public',
                    'revision' => 'Revision',
                    'draft' => 'Draft',
                ),
            ))
            ->add('enter', 'submit');
    }

    /**
     * {@inheritDoc}
     */
    public function fill($form, $article)
    {
        $form->get('category')->setData($article->getCategory());
        $form->get('subject')->setData($article->getSubject());
        $form->get('content')->setData($article->getContent());
        $form->get('status')->setData($article->getStatus());
    }
}
