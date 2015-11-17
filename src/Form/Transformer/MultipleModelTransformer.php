<?php

namespace BZIon\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MultipleModelTransformer implements DataTransformerInterface
{
    /**
     * A transformer for single models
     *
     * @var SingleModelTransformer
     */
    private $transformer;

    /**
     * @param string $type The type of the model (e.g Team)
     */
    public function __construct($type)
    {
        $this->transformer = new SingleModelTransformer($type);
    }

    /**
     * Transforms objects (models) to integers (IDs)
     *
     * @param  Model|Model[]|null $models The models to transform
     * @return int[]
     */
    public function transform($models)
    {
        if (!is_array($models)) {
            $models = array($models);
        }

        return array_map(array($this->transformer, 'transform'), $models);
    }

    /**
     * Transforms IDs to a list of objects
     *
     * @param  int|int[] $ids The model IDs to transform
     * @throws TransformationFailedException if the team is not found.
     * @return Model[]
     */
    public function reverseTransform($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        return array_map(array($this->transformer, 'reverseTransform'), $ids);
    }
}
