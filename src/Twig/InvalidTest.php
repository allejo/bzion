<?php
namespace BZIon\Twig;

use Model;

class InvalidTest
{
    /**
     * Find if a model is invalid
     *
     * @param  Model $model The model we want to test
     * @return bool  True if the model is invalid, false if it is valid
     */
    public function __invoke(Model $model)
    {
        return !$model->isValid();
    }

    public static function get()
    {
        return new \Twig_SimpleTest('invalid', new self());
    }
}
