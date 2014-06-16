<?php
namespace BZIon\Form;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PlayerTransformer implements DataTransformerInterface
{


    /**
     * Transforms an object (model) to an integer (int) .
     *
     * @param  Model|null $model
     * @return int
     */
    public function transform($players)
    {
        if ($players === null) {
            return '';
        }

        if (!is_string($players)) {
            return PlayerType::reverseTransform($players, function($player) {
                return $player->getUsername();
            });
        }
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
        return $id;
    }
}
