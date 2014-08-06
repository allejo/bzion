<?php
namespace BZIon\Twig;

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
