<?php

namespace BZIon\Twig;

class HumanDateFilter
{
    /**
     * @param \TimeDate $time   The TimeDate object we'll be representing as text
     * @param string    $format The format that will be shown. If a format isn't set, it'll return the difference in human readable time
     *
     * @return string
     */
    public function __invoke($time, $format = "")
    {
        $timeFormat = '<span class="c-timestamp js-timestamp" title="%s">%s</span>';

        // If the date is older than 3 weeks ago, we'll automatically spell out the date
        $defaultTimeLiteral = (strtotime($time) < strtotime('-21 day')) ? $time->format(\TimeDate::DATE_FULL) : $time->diffForHumans();

        // If have specified a format, use that format and ignore $defaultTimeLiteral
        $timeLiteral = (empty($format)) ? $defaultTimeLiteral : $time->format($format);

        return sprintf($timeFormat, $time->format("F j, Y g:ia"), $timeLiteral);
    }

    public static function get()
    {
        return new \Twig_SimpleFilter(
            'humanTime',
            new self(),
            array('is_safe' => array('html'))
        );
    }
}
