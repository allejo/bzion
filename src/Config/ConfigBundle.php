<?php
/**
 * This file defines a symfony bundle for bzion configuration
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Config;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony bundle for bzion configurationsymfony bundle for bzion configuration
 */
class ConfigBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        return new ConfigExtension();
    }
}
