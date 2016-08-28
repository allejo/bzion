<?php

namespace BZIon\Twig;

class InstanceOfTest
{
    /**
     * Find if a model is valid
     *
     * @return bool
     */
    public function __invoke($variable, $instance)
    {
        return $variable instanceof $instance;
    }

    public static function get()
    {
        return new \Twig_SimpleTest('instanceof', new self());
    }
}
