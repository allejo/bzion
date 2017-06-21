<?php

namespace BZIon\Twig;

class ArrayValuesFilter
{
    public function __invoke(array $array)
    {
        return array_values($array);
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('array_values', new self());
    }
}
