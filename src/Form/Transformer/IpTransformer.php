<?php
namespace BZIon\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IpTransformer implements DataTransformerInterface
{
    /**
     * Transforms an object (model) to an integer (int) .
     *
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
     * Transforms an ID to an object
     *
     * @return Model
     * @throws TransformationFailedException if the team is not found.
     */
    public function reverseTransform($ips)
    {
        return array_filter(preg_split("/[\s,]+/", strtolower($ips)), function($value) {
            return $value !== '';
        });
    }
}
