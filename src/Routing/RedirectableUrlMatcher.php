<?php
/**
 * This file contains functionality relating to redirecting routes
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher as BaseMatcher;

/**
 * A URL matcher that redirects the user
 */
class RedirectableUrlMatcher extends BaseMatcher
{
    /**
     * Redirects the user to another URL.
     *
     * @param string $path   The path info to redirect to.
     * @param string $route  The route that matched
     * @param string $scheme The URL scheme (null to keep the current one)
     *
     * @return array An array of parameters
     */
    public function redirect($path, $route, $scheme = null)
    {
        $array = parent::redirect($path, $route, $scheme);

        // Make sure that the kernel knows how to properly handle this route
        $array['_defaultHandler'] = true;

        return $array;
    }
}
