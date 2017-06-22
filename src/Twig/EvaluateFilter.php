<?php

namespace BZIon\Twig;

/**
 * A Twig filter that takes a string Twig template and an array for context and renders it.
 *
 * i.e. A Twig filter to render Twig from inside of Twig
 */
class EvaluateFilter
{
    public function __invoke(\Twig_Environment $env, $string, array $context)
    {
        $template = $env->createTemplate($string);

        return $template->render($context);
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('evaluate', new self(), [
            'needs_environment' => true,
            'is_safe' => [
                'evaluate' => true
            ]
        ]);
    }
}
