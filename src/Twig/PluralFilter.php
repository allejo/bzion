<?php

namespace BZIon\Twig;

use Doctrine\Common\Inflector\Inflector;

class PluralFilter
{
    /**
     * Make sure that a number is accompanied with the appropriate grammatical
     * number
     *
     * @param number $number
     * @param string $singular The noun in its singular form
     */
    public function __invoke($singular, $number = null)
    {
        if ($number == 1) {
            return "1 $singular";
        }

        $plural = Inflector::pluralize($singular);

        if ($number === null) {
            return $plural;
        }

        return "$number $plural";
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('plural', new self());
    }
}
