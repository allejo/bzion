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
        $timeElement = '<span class="c-timestamp js-timestamp" title="%s">%s</span>';
        $timeOutput = (strtotime($time) < strtotime('-21 day')) ? $time->format('M j, y') : $time->diffForHumans();
        $outputTime = (empty($format)) ? $timeOutput : $time->format($format);

        return sprintf($timeElement, $time->format("F j, Y g:ia"), $outputTime);
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
