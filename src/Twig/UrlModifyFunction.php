<?php

namespace BZIon\Twig;

class UrlModifyFunction
{
    /**
     * Modify the current page URL
     *
     * @param  array  $parameters An array of parameters to add/modify in the request
     * @return string The HTML link
     */
    public static function __invoke(array $parameters) {
        $attributes = \Service::getRequest()->attributes;
        $query = \Service::getRequest()->query;

        return \Service::getGenerator()->generate(
            $attributes->get('_route'),
            $parameters + $attributes->get('_route_params') + $query->all()
        );
    }

    public static function get()
    {
        return new \Twig_SimpleFunction('url_modify', new self());
    }
}
