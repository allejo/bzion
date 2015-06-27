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
     * Transforms an object (model) to an integer (int) .
     *
     * @param  Model|null $model
     * @return int
     */
    public function transform($model)
    {
        if (null === $model) {
            return 0;
        }

        return $model->getID();
    }

    /**
     * Transforms an ID to an object
     *
     * @param  int                           $id
     * @return Model
     * @throws TransformationFailedException if the team is not found.
     */
    public function reverseTransform($id)
    {
        $id = (int) $id;
        $type = $this->type;

        $model = $type::get($id);

        if (!$model->isValid()) {
            throw new TransformationFailedException(
                "A $type with ID \"$id\" does not exist"
            );
        }

        return $model;
    }
}
