<?php
/**
 * This file contains a validator constraint for model aliases
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Unique alias constraint for models
 */
class UniqueAlias extends Constraint
{
    /**
     * @var string
     */
    public $message = 'There is already an object with that alias';

    /**
     * The type of the model
     *
     * @var string
     */
    public $type;

    /**
     * The model being edited
     *
     * @var AliasModel|null
     */
    public $model;

    /**
     * UniqueAlias
     *
     * @param string           $type The type of the model
     * @param AliasModel|null $model The model itself
     */
    public function __construct($type, $model)
    {
        $this->type = $type;
        $this->model = $model;
    }
}
