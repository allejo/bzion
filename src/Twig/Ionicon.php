<?php

namespace BZIon\Twig;

class Ionicon
{
    private static $svgPath = '/assets/svg';
    private static $svgIcons = [];

    public function __invoke(\Twig_Environment $env, $name, $height = '16px', $width = '16px')
    {
        $svg = DOC_ROOT . self::$svgPath . '/' . $name . '.svg';
        $cacheName = $name . $height . $width;

        if (isset(self::$svgIcons[$cacheName])) {
            return self::$svgIcons[$cacheName];
        }

        if (file_exists($svg)) {
            $svgDoc = simplexml_load_string(file_get_contents($svg));

            if ($svgDoc === false) {
                return '';
            }

            $svgDoc->addAttribute('class', 'ionicon');
            $height && $svgDoc->addAttribute('height', $height);
            $width  && $svgDoc->addAttribute('width', $width);

            self::$svgIcons[$cacheName] = $svgDoc->asXML();

            return self::$svgIcons[$cacheName];
        }

        return '';
    }

    public static function get()
    {
        return new \Twig_SimpleFunction('ionicon', new self(), [
            'needs_environment' => true,
            'is_safe' => ['html']
        ]);
    }
}
