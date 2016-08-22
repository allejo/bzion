<?php

namespace BZIon\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MatchTeamTransformer extends SingleModelTransformer
{
    /**
     * Create new team
     */
    public function __construct()
    {
        parent::__construct('Team');
    }

    /**
     * Transforms an object (model) to an integer (int).
     *
     * @param  Model|null $model
     * @return int
     */
    public function transform($model)
    {
        return parent::transform($model);
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
        if (\ColorTeam::isValidTeamColor($id)) {
            return new \ColorTeam($id);
        }

        return parent::reverseTransform($id);
    }
}
