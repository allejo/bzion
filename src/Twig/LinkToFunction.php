<?php
namespace BZIon\Twig;

class LinkToFunction
{
    /**
     * Get a link literal to a Model
     *
     * @param  \Model  $model     The model we want to link to
     * @param  string  $icon      A font awesome icon identifier to show instead of text
     * @param  string  $action    The action to link to (e.g show or edit)
     * @param  boolean $linkAll   Whether to link to inactive or deleted models
     * @param  string  $class     The CSS class(es) to apply to the link
     * @param  string  $forceText Whether to show both the icon and text
     * @return string  The HTML link
     */
    public function __invoke(
        $context,
        \Model $model,
        $icon = null,
        $action = 'show',
        $linkAll = false,
        $class = '',
        $forceText = false
    ) {
        $content = $this->getContent($model, $icon, $forceText);

        if ($model instanceof \UrlModel && ($linkAll || !isset($context['app']) || $context['app']->getController()->canSee($model))) {
            $params = array();
            if ($linkAll) {
                $params['showDeleted'] = true;
            }

            $url = $model->getURL($action, false, $params);

            return '<a' . $this->getClass($class) . ' href="' . $url . '">' . $content . '</a>';
        }

        return '<span' . $this->getClass("$class disabled-link") . '>' . $content . '</a>';
    }

    /**
     * Get the content of the link to show
     *
     * @param  \Model $model     The model we want to link to
     * @param  string $icon      A font awesome icon identifier to show instead of text
     * @param  string $forceText Whether to show both the icon and text
     * @return string The link's content
     */
    private function getContent($model, $icon, $forceText)
    {
        $content = "";

        if ($icon) {
            $content .= "<i class=\"fa fa-$icon\"></i>";

            if ($forceText) {
                $content .= " ";
            }
        }

        if (!$icon || $forceText) {
            $content .= \Model::escape($this->getModelName($model));
        }

        return $content;
    }

    /**
     * Get the name of any model
     *
     * @param  \Model $model
     * @return string The name of the model
     */
    private function getModelName(\Model $model)
    {
        if ($model instanceof \NamedModel) {
            return $model->getName();
        }
        if ($model instanceof \AliasModel) {
            return $model->getAlias();
        }

        return $model->getId();
    }

    /**
     * Create a CSS class string
     *
     * @param string The CSS class(es), without `class=".."`
     */
    private function getClass($class)
    {
        if (trim($class) == '') {
            return $class;
        }

        return ' class="' . $class . '"';
    }

    public static function get()
    {
        return new \Twig_SimpleFunction('link_to', new self(), array(
            'is_safe'       => array('html'),
            'needs_context' => true
        ));
    }
}
