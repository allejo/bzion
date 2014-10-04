<?php
/**
 * This file allows easily updating the configuration file when new code has
 * been pulled
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Composer;

use BZIon\Config\Configuration;
use Symfony\Component\Process\Process;
use Composer\Script\Event;


use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\ScalarNode;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Yaml;

/**
 * A handler for the ignored config.yml file
 */
class ConfigHandler
{
    private $event;
    private $io;

    public function __construct($event) {
        $this->event = $event;
        $this->io = $event->getIO();
    }

    /**
     * Migrate the config.yml file
     *
     * @param $event Event Composer's event
     */
    public function build()
    {
        $file = realpath(__DIR__ . '/../../app/config.yml');
        $exists = is_file($file);

        $configuration = new Configuration();
        $tree = $configuration->getConfigTreeBuilder()->buildTree();

        $action = $exists ? 'Updating' : 'Creating';
        $this->io->write("<info>$action the \"$file\" file</info>");

        // Load the configuration file if it exists
        $config = $exists ? Yaml::parse($file) : array();

        if (!is_array($config)) {
            $config = array();
        }

        $this->writeNode($tree, $config);

        file_put_contents($file, Yaml::dump($config, 4));
    }

    /**
     * Write the node in the configuration array
     *
     * @param  NodeInterface $node The node to write
     * @param  array $config The parsed configuration
     * @param  string $parent The name of the parent nodes
     * @return void
     */
    private function writeNode(NodeInterface $node, array &$config = array(), $parent = null)
    {
        $name = $node->getName();

        if ($parent) {
            $name = $parent . '.' . $name;
        }

        if (!$node instanceof ArrayNode) {
            if (!array_key_exists($node->getName(), $config)) {
                if ($info = $node->getInfo()) {
                    $this->io->write("\n<info>$info</info>");
                }

                if ($node->hasDefaultValue()) {
                    $default = Inline::dump($node->getDefaultValue());

                    $question = "<question>$name</question> (<comment>$default</comment>): ";
                    $value = $this->io->ask($question, $default);
                } else {
                    $value = $this->io->ask("<question>$name</question>: ");
                }

                $config[$node->getName()] = Inline::parse($value);
            }
        } else {
            if (!isset($config[$node->getName()])) {
                $config[$node->getName()] = array();

                if ($info = $node->getInfo()) {
                    $this->io->write(array(
                        "\n<fg=blue;options=bold>$info",
                        str_repeat('-', strlen($info)) . "</fg=blue;options=bold>",
                    ));
                }
            }

            foreach ($node->getChildren() as $childNode) {
                $this->writeNode($childNode, $config[$node->getName()], $name);
            }
        }
    }
}
