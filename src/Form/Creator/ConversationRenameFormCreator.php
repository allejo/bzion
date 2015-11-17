<?php
/**
 * This file contains a form creator to rename a Conversation's subject
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for conversation renames
 */
class ConversationRenameFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('subject', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array('max' => 50))
                ),
                'data' => $this->editing->getSubject(),
            ))
            ->add('Rename', 'submit')
            ->setAction($this->editing->getUrl());
    }

    /**
     * {@inheritDoc}
     */
    protected function getName()
    {
        return 'rename_form';
    }
}
