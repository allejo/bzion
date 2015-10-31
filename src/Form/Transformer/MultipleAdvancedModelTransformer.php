<?php
namespace BZIon\Form\Transformer;

use Symfony\Component\Form\Exception\TransformationFailedException;

class MultipleAdvancedModelTransformer extends AdvancedModelTransformer
{
    /**
     * A list of Models to include to the results, even if they are not
     * specified by the user
     *
     * @var array
     */
    private $included = array();

    /**
     * Transforms data to an object
     *
     * @param  string $data
     * @return Model[]
     */
    public function reverseTransform($data)
    {
        // Handle the data provided by Javascript, if any
        $transformed = parent::transformJSON($data);

        if ($transformed !== false) {
            return $transformed;
        }

        $models = array();

        foreach ($this->types as $type) {
            if (trim($data[$type]) === '') {
                continue;
            }

            // Array to store IDs for quick access so we can be sure that no
            // duplicates are saved
            $ids = array();

            // Add force-included models
            foreach ($this->included[$type] as $model) {
                $ids[$model->getID()] = true; // Prevent duplication
                $models[] = $model;
            }

            $input = explode(',', $data[$type]);
            $input = array_unique(array_map('trim', $input));

            foreach ($input as $name) {
                if ($name === '') {
                    continue;
                }

                $model = $this->getModelFromName($name, $type);

                if (!$model) {
                    // No model was found matching that name
                    $models[] = $this->invalidModel($type);
                } elseif (!$model->isValid() || !isset($ids[$model->getID()])) {
                    // We can proceed, since we're not storing a duplicate
                    // (We don't check invalid models, since they might be
                    // corresponding to different names)
                    $models[] = $model;
                    $ids[$model->getID()] = true;
                }
            }
        }

        return $models;
    }

    /**
     * Add a model to the list of models that should be included in all cases
     *
     * @param  \Model $model The model to include
     * @throws \Exception When a model is of an unsupported type
     */
    public function addInclude(\Model $model)
    {
        $type = strtolower($model->getType());

        if (!in_array($type, $this->types)) {
            throw new \Exception(
                "Objects of type \"{$model->getTypeForHumans()}\" are not supported"
            );
        }

        $this->included[$type][] = $model;
    }
}
