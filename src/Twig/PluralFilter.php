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
     * @param string|null $plural The noun in its plural form (calculated
     *                            automatically by default)
     */
    public function __invoke($singular, $number = null, $plural = null)
    {
        if ($number == 1) {
            return "1 $singular";
        }

        $plural = $plural ?: Inflector::pluralize($singular);

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
