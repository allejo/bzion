<?php

namespace BZIon\Twig;

class ArrayValuesFilter
{
    public function __invoke($array)
    {
        if (!is_array($array))
        {
            return $array;
        }

        return array_values($array);
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('values', new self());
    }
}
