<?php
/**
 * This file contains a form creator for player invitations to Groups
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\AdvancedModelType;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for group invitations
 */
class GroupInviteFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('players', new AdvancedModelType('player'), array(
                'constraints' => new NotBlank(),
                'multiple'    => true,
            ))
            ->add('Invite', 'submit')
            ->setAction($this->editing->getUrl());
    }

    /**
     * {@inheritDoc}
     */
    protected function getName()
    {
        return 'invite_form';
    }
}
