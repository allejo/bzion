<?php
namespace BZIon\Twig;

use Model;

class ValidTest
{
    /**
     * Find if a model is valid
     *
     * @param  Model $model The model we want to test
     * @return bool
     */
    public function __invoke(Model $model)
    {
        return $model->isValid();
    }

    public static function get()
    {
        return new \Twig_SimpleTest('valid', new self());
    }
}
