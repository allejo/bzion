<?php
/**
 * This file contains a validator constraint that makes sure models are valid
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Model validator for the AdvancedModelType
 */
class ModelValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            // No need to run any checks if no model is provided
            return;
        }

        if (!is_array($value)) {
            $value = array($value);
        }

        if ($constraint->single && count($value) > 1) {
            dump($constraint);
            $this->context
                ->buildViolation($constraint->tooManyMessage)
                ->addViolation();
        }

        foreach ($value as $model) {
            if ($model->isDeleted()) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%type%', $model->getTypeForHumans())
                    ->setParameter('%name%', $model->getName())
                    ->addViolation();
            }
        }
    }
}
