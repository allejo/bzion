<?php
namespace BZIon\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

abstract class AdvancedModelTransformer implements DataTransformerInterface
{
    /**
     * @var string[]
     */
    protected $types;

    /**
     * @param string[] $type     The types of the models
     */
    public function __construct(array $types)
    {
        if (empty($types)) {
            throw new \Exception("No type has been specified");
        }

        $this->types = $types;
    }

    /**
     * Transforms an object (model) to the form representation
     *
     * @param  Model|null $model
     * @return array
     */
    public function transform($models)
    {
        if ($models === null) {
            $models = array();
        } elseif (!is_array($models)) {
            $models = array($models);
        }

        $data = $json = array();
        foreach ($models as $model) {
            $data[$model->getType()][] = $model->getName();
            $json[] = array(
                'id' => $model->getID(),
                'name' => $model->getName(),
                'type' => $model->getType()
            );
        }

        foreach ($data as $type => &$value) {
            $value = implode(', ', $value);
        }

        $data['ids'] = json_encode(array(
            'data' => $json
        ));

        return $data;
    }

    /**
     * Get an invalid model of an acceptable type
     *
     * @param  string|null $type The type of the model, or null to select one of
     *                           the specified types
     * @return \Model
     */
    protected function invalidModel($type = null)
    {
        if ($type === null) {
            $type = reset($this->types); // Get the first value of $this->types
        }

        $type = ucfirst($type);

        return call_user_func(array($type, 'invalid'));
    }

    /**
     * Get a model from its name
     * @param  string     $name The name of the model
     * @param  string     $type The type of the model in lower case
     * @return NamedModel|null  The model or null if no name was specified
     */
    protected function getModelFromName($name, $type)
    {
        if ($name === '') {
            return null;
        }

        if ($type === 'player') {
            return \Player::getFromUsername($name);
        } elseif ($type === 'team') {
            return \Team::getFromName($name);
        } else {
            throw new \InvalidArgumentException('Unsupported model type');
        }
    }

    /**
     * Transform JSON data provided by javascript to a list of Models
     *
     * @param  string $json The JSON provided to us by javascript, containing
     *                      a list of Model IDs and types
     * @return boolean|Model[] A list of models, or false if the data was not
     *                         provided by javascript as JSON
     */
    protected function transformJSON(&$data)
    {
        $json = json_decode($data['ids'], true);

        if (!isset($json['modified']) || $json['modified'] !== true) {
            // The JSON data was not modified; we can proceed to check input
            // from other sources
            return false;
        }

        $models = array();

        foreach ($json['data'] as $key => $object) {
            if ($key === 'modified') {
                continue;
            }

            if (!isset($object['id']) || !isset($object['type'])) {
                throw new TransformationFailedException(
                    "Invalid model provided"
                );
            }

            // Sanity check so that the user can't generate arbitrary classes
            if (!in_array($object['type'], $this->types)) {
                throw new TransformationFailedException(
                    "Objects of type \"{$object['type']}\" are not supported"
                );
            }

            $model = $object['type']::get($object['id']);

            if ($model->isDeleted()) {
                // Show an error message if the model provided by javascript is
                // invalid - we don't let the validator handle this error, so
                // that the user doesn't see a vague warning
                throw new TransformationFailedException(
                    "Invalid model ID provided"
                );
            }

            $models[] = $model;
        }

        return $models;
    }
}
