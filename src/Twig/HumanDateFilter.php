<?php

namespace BZIon\Twig;

class HumanDateFilter
{
    /**
     * @param array     $context Twig's context
     * @param \TimeDate $time    The TimeDate object we'll be representing as text
     * @param string    $format  The format that will be shown. If a format isn't set, it'll return the difference in human readable time
     * @param bool      $tooltip Whether to show a tooltip with the absolute timestamp when a user hovers over it, defaults to false if
     *                           $format is provided and true if it's not.
     *
     *
     * @return string
     */
    public function __invoke($context, $time, $format = "", $tooltip = true)
    {
        if ($context['app']->getController()) {
            $timezone = $context['app']->getController()->getMe()->getTimezone();
            $time = $time->copy()->timezone($timezone);
        }

        $timeFormat = '<span class="c-timestamp js-timestamp"%s>%s</span>';

        // If the date is older than 3 weeks ago, we'll automatically spell out the date
        $defaultTimeLiteral = (strtotime($time) < strtotime('-21 day')) ? $time->format(\TimeDate::DATE_FULL) : $time->diffForHumans();

        // If have specified a format, use that format and ignore $defaultTimeLiteral
        $timeLiteral = (empty($format)) ? $defaultTimeLiteral : $time->format($format);

        // Add a title attribute to the span so that the user sees an accurate representation of the timestamp on hover
        $absoluteTime = $time->format("F j, Y g:ia");
        $tooltipLiteral = ($tooltip) ? " title=\"$absoluteTime\"" : "";

        return sprintf($timeFormat, $tooltipLiteral, $timeLiteral);
    }

    public static function get()
    {
        return new \Twig_SimpleFilter(
            'humanTime',
            new self(),
            array(
                'is_safe'       => array('html'),
                'needs_context' => true
            )
        );
    }
}
