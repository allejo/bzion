<?php
/**
 * This file contains a NotBlank validator for single model types
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlankValidator;

/**
 * Validator that makes sure a model is valid and values are not empty
 */
class NotBlankModelValidator extends NotBlankValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof \Model && !$value->isValid()) {
            // Let the parent class handle the invalid model
            $value = null;
        }

        parent::validate($value, $constraint);
    }
}
