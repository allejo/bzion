<?php
/**
 * This file makes sure that we can read the configuration values in the code
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Config;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * A configuration extension for bzion which makes sure that configuration
 * parameters are accessible in the code
 */
class ConfigExtension extends ConfigurableExtension
{
    private $conf = array();

    /**
     * {@inheritdoc}
     *
     * Loads the configuration from the yml file into container parameters
     */
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $language = new ExpressionLanguage();

        // Evaluate match score modifiers -- this converts strings like "2/3" to
        // the corresponding number
        foreach ($config['league']['duration'] as &$modifier) {
            $modifier = $language->evaluate($modifier);
        }

        $this->store('bzion', $config);
        $container->getParameterBag()->add($this->conf);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'bzion';
    }

    /**
     * Convert the yaml array into proper symfony container parameters
     *
     * @param  string $name  The name of the root parameter
     * @param  mixed  $value The value to store in that parameter
     * @return void
     */
    private function store($name, $value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if (is_int($key)) {
                    // Non-associative arrays are stored as arrays and don't get
                    // expanded further into parameters
                    $this->conf[$name][$key] = $val;
                } else {
                    $this->store("$name.$key", $val);
                }
            }
        } else {
            $this->conf[$name] = $value;
        }
    }
}
