<?php
/**
 * This file defines the format of our configuration file
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                        ->scalarNode('host')->defaultValue('localhost')->isRequired()->attribute('asked', true)->end()
                        ->scalarNode('database')->defaultValue('bzion')->isRequired()->attribute('asked', true)->end()
                        ->scalarNode('username')->defaultValue('bzion_admin')->isRequired()->attribute('asked', true)->end()
                        ->scalarNode('password')->isRequired()->attribute('asked', true)->end()
                    ->end()
                ->end()

                ->arrayNode('site')
                    ->children()
                        ->scalarNode('name')->defaultValue('BZiON')->info('The name of the website')->attribute('asked', true)->end()
                        ->scalarNode('welcome')->defaultValue('Welcome to BZiON')->info('The main welcome message that appears on the landing page')->attribute('asked', true)->end()
                        ->scalarNode('slug')->defaultValue('A good clean League Management System ...sorta')->info('The secondary message that appears on the landing page')->attribute('asked', true)->end()
                        ->arrayNode('alert')
                            ->info('A site-wide alert that will be displayed')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->booleanNode('collapsible')->defaultValue(false)->info('Whether or not the alert can be hidden per browser')->end()
                                ->scalarNode('header')->defaultValue('Alert Title')->info('The title of the alert')->end()
                                ->scalarNode('message')->defaultValue('Sample alert message with information')->info('The message of the alert')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('league')
                    ->isRequired()
                    ->children()
                        ->arrayNode('duration')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->defaultValue(array(
                                20 => '2/3',
                                30 => '3/3'
                            ))
                            ->useAttributeAsKey('minutes')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('email')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('from')
                            ->defaultNull()
                            ->attribute('asked', true)
                            ->info("The e-mail address that will be shown in the 'From:' field when sending messages. Leaving this to null will prevent any e-mails from being sent.")
                            ->example('noreply@example.com')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('api')
                    ->children()
                        ->arrayNode('allowed_ips')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('127.0.0.1', '127.0.1.1'))
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('logging')
                    ->children()
                        ->scalarNode('directory')
                            ->defaultValue('%bzion.root_dir%/app/logs')
                            ->info('The directory where BZiON log files will be stored')
                        ->end()
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

                ->arrayNode('features')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('websocket')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->info("Settings for the PHP web socket")
                            ->children()
                                ->integerNode('pull_port')->defaultValue(8591)->end()
                                ->integerNode('push_port')->defaultValue(8592)->end()
                            ->end()
                        ->end()
                        ->arrayNode('elasticsearch')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->info("Settings for Elasticsearch integration")
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->integerNode('port')->defaultValue(9200)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('testing')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->isRequired()->attribute('manual', true)->end()
                        ->scalarNode('database')->defaultValue('bzion_test')->isRequired()->attribute('manual', true)->end()
                        ->scalarNode('username')->defaultValue('bzion_test_admin')->isRequired()->attribute('manual', true)->end()
                        ->scalarNode('password')->isRequired()->attribute('manual', true)->end()
                    ->end()
                    ->attribute('manual', true)
                ->end()

                ->arrayNode('miscellaneous')
                    ->isRequired()
                    ->children()
                        ->scalarNode('list_server')
                            ->defaultValue('https://my.bzflag.org/db/?action=LIST&version=BZFS0221')
                            ->info('Path to the BZFlag List Server')
                            ->isRequired()
                        ->end()
                        ->scalarNode('admin')
                            ->info('The username of the user who will become the administrator of the instance')
                            ->example('brad')
                            ->attribute('asked', true)
                            ->defaultNull()
                        ->end()
                        ->scalarNode('update_interval')
                            ->defaultValue('5 minutes')
                            ->info('BZFlag server polling interval')
                            ->isRequired()
                        ->end()
                        ->enumNode('development')
                            ->values(array(false, true, 'force'))
                            ->defaultFalse()
                            ->attribute('asked', true)
                            ->info('Whether to enable some functions which make debugging easier')
                            ->attribute(
                                'warning',
                                'Setting this to anything other than false WILL introduce significant security risks and should NOT be done in a production environment'
                            )
                        ->end()
                        ->scalarNode('maintenance')
                            ->defaultFalse()
                            ->info('Whether the website is in maintenance mode (you can set this to a markdown string to display it in the maintenance page)')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
