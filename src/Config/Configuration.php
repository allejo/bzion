<?php
/**
 * This file defines the format of our configuration file
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * BZIon's configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Returns a configuration builder for bzion config files
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bzion');

        $rootNode
            ->children()
                ->arrayNode('mysql')
                    ->isRequired()
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->isRequired()->end()
                        ->scalarNode('database')->isRequired()->end()
                        ->scalarNode('username')->isRequired()->end()
                        ->scalarNode('password')->isRequired()->end()
                    ->end()
                ->end()

                ->arrayNode('site')
                    ->children()
                        ->scalarNode('name')->defaultValue('BZiON')->end()
                    ->end()
                ->end()

                ->arrayNode('league')
                    ->isRequired()
                    ->children()
                        ->arrayNode('duration')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('minutes')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('email')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('from')->defaultNull()->end()
                    ->end()
                ->end()

                ->arrayNode('api')
                    ->children()
                        ->arrayNode('allowed_ips')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('127.0.0.1'))
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('logging')
                    ->children()
                        ->scalarNode('directory')->end()
                        ->enumNode('level')
                            ->values(array(
                                'debug',
                                'info',
                                'notice',
                                'warning',
                                'error',
                                'critical',
                                'alert',
                                'emergency'
                            ))
                            ->defaultValue('notice')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('notifications')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('pusher')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('app_id')->end()
                                ->scalarNode('key')->end()
                                ->scalarNode('secret')->end()
                            ->end()
                        ->end()
                        ->arrayNode('websocket')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->integerNode('pull_port')->defaultValue(8591)->end()
                                ->integerNode('push_port')->defaultValue(8592)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('miscellaneous')
                    ->isRequired()
                    ->children()
                        ->scalarNode('list_server')->isRequired()->end()
                        ->scalarNode('update_interval')->isRequired()->end()
                        ->enumNode('development')
                            ->values(array(false, true, 'force'))
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
