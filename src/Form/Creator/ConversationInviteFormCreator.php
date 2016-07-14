<?php
/**
 * This file contains a form creator for player invitations to Conversations
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\AdvancedModelType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for conversation invitations
 */
class ConversationInviteFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('players', new AdvancedModelType(array('player', 'team')), array(
                'constraints' => new NotBlank(),
                'multiple'    => true,
            ))
            ->add('Invite', SubmitType::class)
            ->setAction($this->editing->getUrl());
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return 'invite_form';
    }
}
