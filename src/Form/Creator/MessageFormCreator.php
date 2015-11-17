<?php
/**
 * This file contains a form creator to create a Message
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for sending messages to conversations
 */
class MessageFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('message', 'textarea', array(
                'constraints' => new NotBlank(
                    array("message" => "You can't send an empty message!"
                ))
            ))
            ->add('Send', 'submit')
            ->setAction($this->editing->getUrl());
    }
}
