<?php
namespace BZIon\Twig;

class LinkToFunction
{
    /**
     * Get a link literal to a Model
     *
     * @param  \UrlModel $model  The model we want to link to
     * @param  string    $icon   A font awesome icon identifier to show instead of text
     * @param  string    $action The action to link to (e.g show or edit)
     * @return string    The <a> tag
     */
    public function __invoke($context, \UrlModel $model, $icon=null, $action='show')
    {
        if ($icon) {
            $content = "<i class=\"fa fa-$icon\"></i>";
        } else {
            $content = \Model::escape($this->getModelName($model));
        }

        if ($context['controller']->canSee($model)) {
            $url = $model->getURL($action);

            return '<a href="' . $url . '">' . $content . '</a>';
        }

        return '<span class="disabled-link">' . $content . '</a>';
    }

    private function getModelName(\UrlModel &$model)
    {
        if ($model instanceof \NamedModel)
            return $model->getName();
        if ($model instanceof \AliasModel)
            return $model->getAlias();

        return $model->getId();
    }

    public static function get()
    {
        return new \Twig_SimpleFunction('link_to', new self(), array(
            'is_safe' => array('html'),
            'needs_context' => true
        ));
    }
}
