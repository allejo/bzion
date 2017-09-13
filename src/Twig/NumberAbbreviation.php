<?php

namespace BZIon\Twig;

class NumberAbbreviation
{
    public function __invoke($number, $precision = 1, $noun = null, $content = null)
    {
        if ($number < 1000) {
            return $number;
        }

        $abbr = '';
        $divisor = 1;
        $divisors = [
            pow(1000, 1) => 'K',  // Thousand
            pow(1000, 2) => 'M',  // Million
            pow(1000, 3) => 'B',  // Billion
            pow(1000, 4) => 'T',  // Trillion
            pow(1000, 5) => 'Qa', // Quadrillion
            pow(1000, 6) => 'Qi', // Quintillion
        ];

        // Loop through each $divisor and find the lowest amount that matches
        foreach ($divisors as $divisor => $abbr) {
            if (abs($number) < ($divisor * 1000)) {
                break; // We found a match!
            }
        }

        // We found our match, or there were no matches.
        $value = number_format($number / $divisor, $precision) . $abbr;

        // English setup
        $nounUsed = '';
        if ($noun !== null) {
            $plural = new PluralFilter();
            $nounUsed = $plural($noun);
        }

        $title = trim(implode(' ', [number_format($number), $nounUsed, $content]));

        return "<span title='{$title}'>{$value}</span>";
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('number_abbr', new self(), [
            'is_safe' => ['html']
        ]);
    }
}
