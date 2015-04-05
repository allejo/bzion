<?php

class ArrayLengthConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * @var int
     */
    protected $actualValue;

    /**
     * @var int
     */
    protected $expectedValue;

    /**
     * @param  int $expected
     * @param  int $actual
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($expected, $actual)
    {
        if (!is_int($expected)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        parent::__construct();
        $this->expectedValue = $expected;
        $this->actualValue   = $actual;
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
        return (count($other) == $this->expectedValue);
    }

    /**
     * @param mixed $other
     *
     * @return string
     */
    public function failureDescription($other)
    {
        return sprintf(
            '%s but expecting length %d',
            $this->toString(),
            $this->expectedValue
        );
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf('the given array has length %d', $this->actualValue);
    }
}
