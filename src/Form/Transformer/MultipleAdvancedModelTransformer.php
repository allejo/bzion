<?php
namespace BZIon\Form\Transformer;

use Symfony\Component\Form\Exception\TransformationFailedException;

class MultipleAdvancedModelTransformer extends AdvancedModelTransformer
{
    /**
     * Transforms data to an object
     *
     * @param  string $data
     * @return Model[]
     * @throws TransformationFailedException if the team is not found.
     */
    public function reverseTransform($data)
    {
        // Handle the data provided by Javascript, if any
        if ($transformed = parent::transformJSON($data)) {
            return $transformed;
        }

        $models = array();

        foreach ($this->types as $type) {
            if (trim($data[$type]) === '') {
                continue;
            }

            foreach (explode(',', $data[$type]) as $name) {
                $models[] = $this->getModelFromName($name, $type)
                         ?: $this->invalidModel($type);
            }
        }

        return $models;
    }
}
