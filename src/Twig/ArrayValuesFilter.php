<?php

namespace BZIon\Twig;

class ArrayValuesFilter
{
    public function __invoke($array, $column = null)
    {
        if (!is_array($array)) {
            return $array;
        }

        if ($column === null || empty($column)) {
            return array_values($array);
        }

        return array_column($array, $column);
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('values', new self());
    }
}
