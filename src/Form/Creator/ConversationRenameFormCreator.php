<?php
/**
 * This file contains a form creator to rename a Conversation's subject
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for conversation renames
 */
class ConversationRenameFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('subject', TextType::class, array(
                'constraints' => array(
                    new NotBlank(), new Length(array('max' => 50))
                ),
                'data' => $this->editing->getSubject(),
            ))
            ->add('Rename', SubmitType::class)
            ->setAction($this->editing->getUrl());
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return 'rename_form';
    }
}
