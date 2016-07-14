<?php
/**
 * This file contains a form creator for Player admin notes
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Form creator for a player's admin notes
 */
class PlayerAdminNotesFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('notes', TextareaType::class, array(
                'data'     => $this->editing->getAdminNotes(),
                'required' => false,
            ))
            ->add('save_and_sign', SubmitType::class, array(
                'label' => 'Save & Sign',
            ))
            ->add('save', SubmitType::class);
    }
}
