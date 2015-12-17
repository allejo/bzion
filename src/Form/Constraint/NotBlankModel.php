<?php
/**
 * This file contains a NotBlank validator constraint for single model types
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Constraint;

use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Constraint that makes sure a model is valid
 */
class NotBlankModel extends NotBlank
{
}
