<?php
/**
 * This file contains a form creator for Player admin notes
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

/**
 * Form creator for a player's admin notes
 */
class PlayerAdminNotesFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('notes', 'textarea', array(
                'data'     => $this->editing->getAdminNotes(),
                'required' => false,
            ))
            ->add('save_and_sign', 'submit', array(
                'label' => 'Save & Sign',
            ))
            ->add('save', 'submit');
    }
}
