<?php
/**
 * This file contains functionality related to parsing Markdown across BZiON
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon;

/**
 * This class extends Parsedown and adds functionality to enable or disable functionality
 */
class MarkdownEngine extends \Parsedown
{
    /**
     * Whether or not to allow images to be rendered. If set to false, it'll be rendered as a hyperlink
     * @var bool
     */
    protected $allowImages;

    /**
     * Overload the function to either return nothing (if image image rendering is disable) or return the
     * rendered image by calling the Parsedown function
     *
     * @param string $Excerpt The excerpt of text to be processed as an image
     *
     * @return array|null|void
     */
    protected function inlineImage($Excerpt)
    {
        if ($this->allowImages) {
            return parent::inlineImage($Excerpt);
        }

        return null;
    }

    /**
     * Disable or enable Parsedown from rendering images
     *
     * @param bool $allowImages Whether to allow images to be rendered or not
     */
    public function setAllowImages($allowImages)
    {
        $this->allowImages = $allowImages;
    }
}
