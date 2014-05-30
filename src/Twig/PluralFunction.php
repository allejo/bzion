<?php
namespace BZIon\Twig;

class PluralFunction
{
    /**
     * Make sure that a number is acoompanied with the appropriate grammatical
     * number
     *
     * @param number $number
     * @param string $singular The noun in its singular form
     * @param string|null $plural The noun in its plural form (defaults to adding
     *                            an 's' in the end of the singular noun)
     */
    public function __invoke($number, $singular, $plural=null)
    {
        if (!$plural)
            $plural = $singular . "s";

        if ($number == 1)
            return "1 $singular";
        return "$number $plural";
    }

    public static function get()
    {
        return new \Twig_SimpleFunction('plural', new self());
    }
}
