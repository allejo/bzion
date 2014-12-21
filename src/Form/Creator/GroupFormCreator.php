<?php
/**
 * This file contains a form creator to create a Group
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\PlayerType;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for creating new conversations
 */
class GroupFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        $notBlank = array('constraints' => new NotBlank());

        return $builder
            ->add('Recipients', new PlayerType(), array(
                'constraints' => new NotBlank(array(
                    'message' => 'You need to specify the recipients of your message'
                )),
                'multiple' => true,
                'include'  => $this->editing,
            ))
            ->add('Subject', 'text', $notBlank)
            ->add('Message', 'textarea', $notBlank)
            ->add('Send', 'submit')
            // Prevents JS from going crazy if we load a page with AJAX
            ->setAction(\Service::getGenerator()->generate('message_list'));
    }
}
