<?php

namespace BZIon\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class IpTransformer implements DataTransformerInterface
{
    /**
     * Transforms IP addresses from an array to a comma-separated string
     *
     * @param  string[] A list of IP addresses
     * @return string
     */
    public function transform($ips)
    {
        if (!$ips) {
            return '';
        }

        return implode(', ', $ips);
    }

    /**
     * Transforms a comma-separated list of IP addresses to an array
     *
     * @param  string $ips A comma-separated list of IP addresses
     * @return string[]
     */
    public function reverseTransform($ips)
    {
        return array_filter(preg_split("/[\s,]+/", strtolower($ips)), function ($value) {
            // Filter out empty IP addresses
            return $value !== '';
        });
    }
}
