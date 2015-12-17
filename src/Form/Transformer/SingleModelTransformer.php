<?php

namespace BZIon\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SingleModelTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type The type of the model (e.g Team)
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Transforms an object (model) to an integer (int).
     *
     * @param  Model|null $model
     * @return int
     */
    public function transform($model)
    {
        if (empty($model)) {
            return 0;
        }

        return $model->getID();
    }

    /**
     * Transforms an ID to an object
     *
     * @param  int                           $id
     * @throws TransformationFailedException if the team is not found.
     * @return Model
     */
    public function reverseTransform($id)
    {
        $id = (int) $id;

        $type = $this->type;

        // We don't need to check for the validity of the model, since Symfony
        // already checks if it's a member of the list we provided
        $model = $type::get($id);

        return $model;
    }
}
