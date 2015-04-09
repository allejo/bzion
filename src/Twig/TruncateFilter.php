<?php

namespace BZIon\Twig;

class TruncateFilter
{
    /**
     * Generate a summary of the given string
     *
     * @param  string  $string The string to summarise
     * @param  integer $length The maximum length of the returned string
     * @return string          The summarised string
     */
    public function __invoke($string, $length = 50)
    {
        if (mb_strlen($string) > $length) {
            return trim(mb_substr($string, 0, $length-1)) . "...";
        }

        return $string;
    }

    public static function get()
    {
        return new \Twig_SimpleFilter(
            'truncate',
            new self()
        );
    }
}
