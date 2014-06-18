<?php
namespace BZIon\Form;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IpTransformer implements DataTransformerInterface
{

    /**
     * Transforms an object (model) to an integer (int) .
     *
     * @param  Model|null $model
     * @return int
     */
    public function transform($ips)
    {
        if (!$ips)
            return '';

        return implode(', ', $ips);
    }

    /**
     * Transforms an ID to an object
     *
     * @param  int                           $id
     * @return Model
     * @throws TransformationFailedException if the team is not found.
     */
    public function reverseTransform($ips)
    {
        $array = explode(',', $ips);
        $array = array_map('trim', $array);

        return array_filter($array);
    }
}
