<?php
/**
 * This file allows easily updating the configuration file when new code has
 * been pulled
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Composer;

use BZIon\Config\Configuration;
use Composer\Script\Event;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\ScalarNode;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Yaml;

/**
 * A handler for the ignored config.yml file
 */
class ConfigHandler
{
    private $event;
    private $io;

    /**
     * Whether the user has received the short notice about configuration values
     * being able to get edited later on app/config.yml
     * @var bool
     */
    private $questionInfoShown;

    const CAUTION_LINE_LENGTH = 60;

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
        $file = realpath(__DIR__ . '/../../app') . '/config.yml';
        $exists = is_file($file);

        $configuration = new Configuration();
        $tree = $configuration->getConfigTreeBuilder()->buildTree();

        $action = $exists ? 'Updating' : 'Creating';
        $this->io->write(" <info>$action the \"$file\" file</info>\n");

        // Load the configuration file if it exists
        $config = $exists ? Yaml::parse($file) : array();

        if (!is_array($config)) {
            $config = array();
        }

        $this->writeNode($tree, $config);

        file_put_contents($file, Yaml::dump($config, 4));

        $this->io->write(<<<SUCCESS
<bg=green;options=bold>                                            </>
<bg=green;options=bold> [OK] The configuration file is up to date  </>
<bg=green;options=bold>                                            </>
SUCCESS
        );
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

        if (!$node instanceof ArrayNode || $node instanceof PrototypedArrayNode) {
            if (!array_key_exists($node->getName(), $config)) {
                $config[$node->getName()] = $this->writeNodeQuestion($node, $name);
            }
        } else {
            if (!isset($config[$node->getName()])) {
                $config[$node->getName()] = array();
            }

            foreach ($node->getChildren() as $childNode) {
                $this->writeNode($childNode, $config[$node->getName()], $name);
            }
        }
    }

    /**
     * Present a node question to the user
     *
     * @param  VariableNode $node The node in question
     * @param  string $name The name of the node
     * @return mixed The new value of the node
     */
    private function writeNodeQuestion($node, $name)
    {
        if (!$this->questionInfoShown) {
            $this->io->write(array(
                "<comment> ! [NOTE] You can change all the configuration options later",
                " ! on the config.yml file in the app folder</>\n"
            ));

            $this->questionInfoShown = true;
        }

        if (!$node->getAttribute('asked')) {
            return $node->getDefaultValue();
        }

        $this->writeWarning($node);

        if ($info = $node->getInfo()) {
            $this->io->write(" $info");
        }

        if ($example = $node->getExample()) {
            // We use Inline::dump() to convert the value to the YAML
            // format so that it is readable by the user (as an example,
            // false values are converted to 'false' instead of an empty
            // string)
            $example = Inline::dump($example);
            $this->io->write(" Example: <comment>$example</comment>");
        }

        $question = " <fg=green>$name";

        if ($node instanceof EnumNode) {
            // Create list of possible values for enum configuration parameters
            $values = $node->getValues();

            foreach ($values as &$value) {
                $value = Inline::dump($value);
            }

            $question .= ' (' . implode(', ', $values) . ')';
        }

        $question .= "</fg=green>";

        if ($node->hasDefaultValue()) {
            // Show the default value of the parameter
            $default = Inline::dump($node->getDefaultValue());
            $question .= " [<comment>$default</comment>]";
        } else {
            $default = null;
        }

        // Show a user-friendly prompt
        $question .= ":\n > ";

        $value = $this->io->askAndValidate($question, function($value) use ($node) {
            $value = Inline::parse($value);

            // Make sure that there are no errors
            $node->finalize($value);

            return $value;
        }, false, $default);

        $this->io->write("");

        return $value;
    }

    /**
     * Write a warning
     */
    private function writeWarning($node)
    {
        if (!$node->hasAttribute('warning')) {
            return;
        }

        // Split warning into words so that we can apply wrapping
        $words = preg_split('/\s+/', $node->getAttribute('warning'));

        $caution = ' ! [CAUTION]';
        $currentLength = 0;

        foreach ($words as $word) {
            if ($currentLength > self::CAUTION_LINE_LENGTH) {
                $caution .= "\n !";
                $currentLength = 0;
            }

            $caution .= ' ' . $word;
            $currentLength += strlen($word) + 1;
        }

        $this->io->write("<warning>\n\n$caution\n</>");
    }
}
