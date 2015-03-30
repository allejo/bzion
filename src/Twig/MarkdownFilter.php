<?php

namespace BZIon\Twig;

class MarkdownFilter
{
    public function __invoke($string, $escapeHTML = true)
    {
        $ParseEngine = new \Parsedown();
        $ParseEngine->setMarkupEscaped($escapeHTML);

        return $ParseEngine->text($string);
    }

    public static function get()
    {
        return new \Twig_SimpleFilter(
            'markdown',
            new self(),
            array('is_safe' => array('html'))
        );
    }
}
