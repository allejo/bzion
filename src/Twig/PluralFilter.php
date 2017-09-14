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
     * @param bool   $hideNumber Whether or not to hide the number from the return value
     *
     * @return string
     */
    public function __invoke($singular, $number = null, $plural = null, $hideNumber = null)
    {
        if ($number == 1) {
            return "1 $singular";
        }

        $plural = $plural ?: Inflector::pluralize($singular);

        if ($number === null || $hideNumber) {
            return $plural;
        }

        return "$number $plural";
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('plural', new self());
    }
}
