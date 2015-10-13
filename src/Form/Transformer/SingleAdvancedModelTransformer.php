<?php
namespace BZIon\Form\Transformer;

use Symfony\Component\Form\Exception\TransformationFailedException;

class SingleAdvancedModelTransformer extends AdvancedModelTransformer
{
    /**
     * Transforms data to an object
     *
     * @param  string $data
     * @return Model|null
     * @throws TransformationFailedException if the team is not found.
     */
    public function reverseTransform($data)
    {
        // Handle the data provided by Javascript, if any
        if ($transformed = parent::transformJSON($data)) {
            if (count($transformed) > 1) {
                // Return array so that the model validator can show an error
                // (throwing an exception here would not show a proper message
                // to the user)
                return $transformed;
            } else {
                return $transformed[0];
            }
        }

        foreach ($this->types as $type) {
            $model = $this->getModelFromName($data[$type], $type);

            if ($model !== null) {
                return $model;
            }
        }

        // No models found
        return null;
    }
}
