<?php

namespace BZIon\Form\Transformer;

use Symfony\Component\Form\Exception\TransformationFailedException;

class SingleAdvancedModelTransformer extends AdvancedModelTransformer
{
    /**
     * Transforms data to an object
     *
     * @param  string $data
     * @throws TransformationFailedException if the team is not found.
     * @return \Model|null
     */
    public function reverseTransform($data)
    {
        // Handle the data provided by Javascript, if any
        $transformed = self::transformJSON($data);

        if ($transformed !== false) {
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
            $model = $this->getModelFromName(trim($data[$type]), $type);

            if ($model !== null) {
                return $model;
            }
        }

        // No models found
        return null;
    }
}
