<?php

namespace BZIon\Twig;

/**
 * A twig global that provides information about the app
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            HumanDateFilter::get(),
            TruncateFilter::get(),
            MarkdownFilter::get(),
            PluralFilter::get(),
            YesNoFilter::get()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            LinkToFunction::get(),
            UrlModifyFunction::get()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array(
            ValidTest::get(),
            InvalidTest::get(),
            InstanceOfTest::get()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bzion_extension';
    }

    public function getOperators()
    {
        return array(
            array(),
            array(
                '~~'  => array('precedence' => 20, 'class' => '\BZIon\Twig\ModelEqualityOperator', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
                '~/~' => array('precedence' => 20, 'class' => '\BZIon\Twig\ModelInequalityOperator', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
            ),
        );
    }
}
