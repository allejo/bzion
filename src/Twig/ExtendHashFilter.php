<?php

namespace BZIon\Twig;

/**
 * A Twig filter that when given a base associative array (a.k.a. hash) will recursively merge another array recursively
 * on top of the base. This is different from using the `default` filter which only sets a value when the variable is
 * null.
 */
class ExtendHashFilter
{
    public function __invoke($array, $base)
    {
        if (!is_array($array))
        {
            $array = [$array];
        }

        return array_merge_recursive($base, $array);
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('extend_hash', new self());
    }
}
