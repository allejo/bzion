<?php

namespace BZIon\Twig;

class CanonicalUrl
{
    /**
     * Build the canonical URL of a relative URL
     *
     * @param  string $relativeUrl
     * @return string The canonical URL
     */
    public function __invoke($relativeUrl)
    {
        $baseURL = \Service::getRequest()->getSchemeAndHttpHost();

        return $baseURL . $relativeUrl;
    }

    public static function get()
    {
        return new \Twig_SimpleFilter(
            'canonical',
            new self()
        );
    }
}