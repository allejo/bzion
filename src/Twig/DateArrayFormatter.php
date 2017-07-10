<?php

namespace BZIon\Twig;

class DateArrayFormatter
{
    public function __invoke(array $dates, $format)
    {
        foreach ($dates as &$date)
        {
            $dt = new \DateTime($date);
            $date = $dt->format($format);
        }

        return $dates;
    }

    public static function get()
    {
        return (new \Twig_SimpleFilter('date_array_formatter', new self()));
    }
}
