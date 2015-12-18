<?php

namespace BZIon\Twig;

/**
 * A twig operator to test if two models are the same (they are of the same type
 * and have the same ID)
 */
class ModelEqualityOperator extends \Twig_Node_Expression
{
    /**
     * {@inheritdoc}
     */
    public function __construct(\Twig_NodeInterface $left, \Twig_NodeInterface $right, $lineno)
    {
        parent::__construct(array('left' => $left, 'right' => $right), array(), $lineno);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->raw('( \BZIon\Twig\ModelEqualityOperator::equalModels((')
            ->subcompile($this->getNode('left'))
            ->raw('),(')
            ->subcompile($this->getNode('right'))
            ->raw(')) )')
        ;
    }

    /**
     * Checks if $a and $b represent the same object
     *
     * Used as a helper function, since there might be syntax errors when older
     * PHP versions try to parse this: (new Model())->isSameAs($b)
     *
     * @param \Model $a
     * @param \Model $b
     *
     * @return bool
     */
    public static function equalModels(\Model $a, \Model $b) {
        return $a->isSameAs($b);
    }
}
