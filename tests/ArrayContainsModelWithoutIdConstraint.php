<?php

class ArrayContainsModelWithoutIdConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * @var int
     */
    protected $value;

    /**
     * @param  int                         $value
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($value)
    {
        if (!is_int($value)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        parent::__construct();
        $this->value = $value;
    }

    /**
     * Evaluates the constraint for parameter $other.
     * Returns TRUE if the constraint is met, FALSE otherwise.
     *
     * @param  mixed $other Value or object to evaluate.
     * @return bool
     */
    public function matches($other)
    {
        $foundModel = false;

        foreach ($other as $model) {
            if ($this->value == $model->getId()) {
                $foundModel = true;
            }
        }

        return !$foundModel;
    }

    /**
     * @param mixed $other
     *
     * @return string
     */
    public function failureDescription($other)
    {
        return sprintf(
            'the given array of Models contains at least one Model with ID %d',
            $this->value
        );
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf('has no Models with ID %d', $this->value);
    }
}
