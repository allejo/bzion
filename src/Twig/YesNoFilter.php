<?php
namespace BZIon\Twig;

class YesNoFilter
{
    /**
     * Convert a boolean into a readable yes/no value
     *
     * @param  boolean $boolean
     * @return string  Yes or No
     */
    public function __invoke($boolean)
    {
        return $boolean ?
               "Yes"    :
               "No";
    }

    public static function get()
    {
        return new \Twig_SimpleFilter('yesNo', new self());
    }
}
