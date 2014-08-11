<?php
namespace BZIon\Twig;

/**
 * A twig global that enables the creation of Models from their IDs
 *
 * Example usage: `fetcher.newsCategory(15).id`
 */
class ModelFetcher
{
    public function __call($type, $arguments)
    {
        $id = $arguments[0];

        if ($id instanceof \Model) {
            return $id;
        }

        $class = new \ReflectionClass("\\" . ucfirst($type));

        if (!$class->isSubclassOf("\\Model")) {
            throw new Exception("$type is not a model");
        }

        return $class->newInstance($id);
    }
}
