<?php

namespace BZIon\Twig;

use BZIon\MarkdownEngine;

class MarkdownFilter
{
    public function __invoke($string, $escapeHTML = true, $allowImages = true)
    {
        $ParseEngine = new MarkdownEngine();
        $ParseEngine->setAllowImages($allowImages);
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
