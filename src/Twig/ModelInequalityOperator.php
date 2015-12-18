<?php

namespace BZIon\Twig;

/**
 * A twig operator to test if two models are not the same (they are not of the
 * same type or have a different ID)
 */
class ModelInequalityOperator extends ModelEqualityOperator
{
    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->raw('(!');
        parent::compile($compiler);
        $compiler->raw(')');
    }
}

