<?php
namespace BZIon\Twig;

class LinkToFunction
{
    /**
     * Get a link literal to a Model
     *
     * @param UrlModel $model The model we want to link to
     * @param string $singular The noun in its singular form
     * @param string|null $plural The noun in its plural form (defaults to adding
     *                            an 's' in the end of the singular noun)
     */
    public function __invoke(\UrlModel $model)
    {
        $url  = $model->getURL();
        $name = \Model::escape($this->getModelName($model));

        return '<a href="' . $url . '">' . $name . '</a>';
    }

    private function getModelName(\UrlModel &$model)
    {
        if ($model instanceof \Player)
            return $model->getUsername();
        if ($model instanceof \News)
            return $model->getSubject();

        return $model->getName();
    }

    public static function get()
    {
        return new \Twig_SimpleFunction('link_to', new self(), array('is_safe' => array('html')));
    }
}
