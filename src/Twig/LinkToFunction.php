<?php

namespace BZIon\Twig;

class LinkToFunction
{
    /**
     * Get a link literal to a Model
     *
     * @param          $context
     * @param  \Model  $model     The model we want to link to
     * @param  string  $icon      A font awesome icon identifier to show instead of text
     * @param  string  $action    The action to link to (e.g show or edit)
     * @param  bool $linkAll   Whether to link to inactive or deleted models
     * @param  string  $class     The CSS class(es) to apply to the link
     * @param  bool $forceText Whether to show both the icon and text
     * @param  string  $content   Override the content that will automatically be used
     *
     * @return string The HTML link
     */
    public function __invoke(
        $context,
        $model,
        $icon = null,
        $action = 'show',
        $linkAll = false,
        $class = '',
        $forceText = false,
        $content = ''
    ) {
        if ($content === '' || $content === null) {
            $content = $this->getContent($model, $icon, $forceText);
        } elseif ($icon) {
            $content = "<i class=\"fa fa-$icon\" aria-hidden=\"true\"></i> " . $content;
        }

        if ($this->isLinkable($model, $linkAll, $context)) {
            $params = array();
            if ($linkAll) {
                $params['showDeleted'] = true;
            }

            $url = $model->getURL($action, false, $params);

            return '<a' . $this->getClass($class) . ' href="' . $url . '">' . $content . '</a>';
        }

        return '<span' . $this->getClass("$class disabled-link") . '>' . $content . '</span>';
    }

    /**
     * Get the content of the link to show
     *
     * @param  \Model  $model     The model we want to link to
     * @param  string  $icon      A font awesome icon identifier to show instead of text
     * @param  bool $forceText Whether to show both the icon and text
     * @return string  The link's content
     */
    private function getContent($model, $icon, $forceText)
    {
        $content = "";

        if ($icon) {
            $content .= "<i class=\"fa fa-$icon\" aria-hidden=\"true\"></i>";

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
    private function getModelName($model)
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
     * @param  $class string The CSS class(es), without `class=".."`
     * @return string
     */
    private function getClass($class)
    {
        if (trim($class) == '') {
            return $class;
        }

        return ' class="' . $class . '"';
    }

    /**
     * Find out if a link should be provided to an object, instead of just a
     * reference to its name
     *
     * @param  \Model $model   The model to test
     * @param  bool   $linkAll Whether to link deleted and inactive models
     * @param  array  $context Twig's context
     * @return bool
     */
    private function isLinkable($model, $linkAll, &$context)
    {
        // Models that don't have a URL can't be linked
        if (!$model instanceof \UrlModel) {
            return false;
        }

        if ($linkAll) {
            return true;
        }

        if (!$context['app']) {
            // Only link active models by default
            return $model->isActive();
        }

        return $context['app']->getController()->canSee($model);
    }

    public static function get()
    {
        return new \Twig_SimpleFunction('link_to', new self(), array(
            'is_safe'       => array('html'),
            'needs_context' => true
        ));
    }
}
