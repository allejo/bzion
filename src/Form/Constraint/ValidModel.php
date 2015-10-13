<?php
/**
 * This file contains a validator constraint for advanced model types
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Valid model constraint for the AdvancedModelType
 */
class ValidModel extends Constraint
{
    /**
     * Whether the input should accept only one model
     *
     * @var bool
     */
    public $single = false;

    /**
     * @var string
     */
    public $message = 'There is no %type% called "%name%"';

    /**
     * @var string
     * @todo Add the acceptable object types
     */
    public $tooManyMessage = 'You can only provide one object';

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'BZIon\Form\Constraint\ModelValidator';
    }
}
