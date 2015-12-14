<?php
/**
 * This file contains a validator constraint that makes sure aliases are unique
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Unique alias validator for models
 */
class UniqueAliasValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            // No need to run any checks if no alias is provided
            return;
        }

        $database = \Database::getInstance();
        $type = $constraint->type;
        $table = $type::TABLE;

        if ($constraint->model && $constraint->model->isValid()) {
            // A model is being edited, make sure we don't show an error because
            // its alias is found in the database
            $results = $database->query(
                "SELECT EXISTS(SELECT 1 FROM $table WHERE alias = ? AND id != ?) AS 'exists'",
                'si',
                array($value, $constraint->model->getId())
            );
        } else {
            $results = $database->query(
                "SELECT EXISTS(SELECT 1 FROM $table WHERE alias = ?) AS 'exists'",
                's',
                $value);
        }

        if ($results[0]['exists']) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
