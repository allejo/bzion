<?php

class ArraysHaveEqualValuesConstraint extends PHPUnit_Framework_Constraint
{
    /**
     *
     * @var array|ArrayAccess
     */
    protected $value;

    /**
     *
     * @param  array|ArrayAccess           $value
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($value)
    {
        if (!(is_array($value) || $value instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'array or ArrayAccess');
        }

        $this->value = $value;
    }

    /**
     * Evaluates the constraint for parameter $other.
     * Returns TRUE if the constraint is met, FALSE otherwise.
     *
     * @param  mixed $other
     *                      Value or object to evaluate.
     * @return bool
     */
    public function matches($other)
    {
        if (count($this->value) != count($other)) {
            return false;
        }

        return count(array_diff($this->value, $other)) == 0;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'has the same values with ' .
                   PHPUnit_Util_Type::export($this->value);
    }
}
