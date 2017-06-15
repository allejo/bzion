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
     * Store all of the settings related to Camo
     * @var array
     */
    private $camo;

    /**
     * Constructor for our extension of Parsedown
     */
    public function __construct()
    {
        $this->camo = array();
        $this->camo['enabled'] = \Service::getParameter('bzion.features.camo.enabled');

        if ($this->camo['enabled']) {
            $this->camo['key'] = \Service::getParameter('bzion.features.camo.key');
            $this->camo['base_url'] = \Service::getParameter('bzion.features.camo.base_url');
            $this->camo['whitelisted_domains'] = \Service::getParameter('bzion.features.camo.whitelisted_domains');
        }
    }

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
            $Image = parent::inlineImage($Excerpt);

            if ($this->camo['enabled']) {
                $parts = parse_url($Image['element']['attributes']['src']);

                if (!isset($parts['host']) || strlen($parts['host']) === 0) {
                    return null;
                }

                if (!in_array($parts['host'], $this->camo['whitelisted_domains'])) {
                    $Image['element']['attributes']['src'] = $this->camo['base_url'] . hash_hmac('sha1', $Image['element']['attributes']['src'], $this->camo['key']) . '/' . bin2hex($Image['element']['attributes']['src']);
                }
            }

            return $Image;
        }

        return null;
    }

    /**
     * Overload the function replace the scheme + host of the link if belongs to the current website serving the link.
     * This will prevent CSS rules from treating full URLs as external links.
     *
     * @param array $Excerpt The structure of the link to be processed
     *
     * @return array
     */
    protected function inlineLink($Excerpt)
    {
        $link = parent::inlineLink($Excerpt);

        if (isset($link['element']['attributes']['href'])) {
            $href = &$link['element']['attributes']['href'];

            if (strpos($href, $scheme = \Service::getRequest()->getSchemeAndHttpHost()) === 0) {
                $href = str_replace($scheme, '', $href);
            }
        }

        return $link;
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
