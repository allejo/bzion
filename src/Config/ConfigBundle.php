<?php
/**
 * This file defines a symfony bundle for bzion configuration
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Config;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony bundle for bzion configuration
 *
 * Allows parsing and validating a configuration file with a complex structure
 */
class ConfigBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new ConfigExtension();
    }
}
