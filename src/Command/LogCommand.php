<?php

namespace BZIon\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Yaml;

class LogCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bzion:log')
            ->setDescription('Add a change to the changelog.yml file')
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'The type of the change (feature, bug or note)'
            )
            ->addArgument(
                'description',
                InputArgument::OPTIONAL,
                'A short description of the change'
            )
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_OPTIONAL,
                'The date when the change occured',
                'today'
            )
            ->addOption(
                'changelog',
                'c',
                InputOption::VALUE_OPTIONAL,
                'The path to the changelog file',
                dirname(dirname(__DIR__)) . '/app/changelog.yml'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $date = \TimeDate::from($input->getOption('date'))->format("j F Y");
        $description = $input->getArgument('description');

        $helper = $this->getHelper('question');

        if (!$type) {
            $question = new ChoiceQuestion(
                '<question>Please enter the type of the change</question>: ',
                array('Bug', 'Feature', 'Note')
            );
            $type = $helper->ask($input, $output, $question);
        }

        if (!$description) {
            $question = new Question('<question>Please enter a short description of the change</question>: ');
            $description = $helper->ask($input, $output, $question);
        }

        $category = $this->getTypeName($type);

        // Parse the current changelog and append the new change to it
        $changelog = Yaml::parse(file_get_contents($input->getOption('changelog')));
        $this->addMissingArrays($changelog, $date, $category);
        $changelog[$date][$category][] = $description;

        $this->sort($changelog);

        $yaml = Yaml::dump($changelog, 3);
        $yaml = preg_replace('/^\'([[:print:]]+)\':$/m', '$1:', $yaml);
        $yaml = preg_replace('/^( {8}- )\'(.*)\'$/m', '$1$2', $yaml);
        $yaml = str_replace("''", "'", $yaml);

        if ($output->isDebug()) {
            $output->writeln($yaml);
        }

        file_put_contents($input->getOption('changelog'), $yaml);

        $output->writeln('<info>The changelog has been updated successfully</info>');
    }

    /**
     * Get a type name to put into the YAML file from whatever the user gave us
     *
     * @param  string $type The user's input
     * @return string
     * @throws \RuntimeException
     */
    private function getTypeName($type)
    {
        switch (strtolower($type)) {
            case 'b':
            case 'bug':
            case 'bugs':
                return 'Bugfixes';
            case 'f':
            case 'feature':
            case 'features':
                return 'Features';
            case 'n':
            case 'note':
            case 'notes':
                return 'Notes';
            default:
                throw new \RuntimeException("I don't understand what '$type' means");
        }
    }

    /**
     * If the changelog.yml file doesn't contain the date and the type of the
     * change, add them to it
     *
     * @param array $changelog The parsed YAML file
     */
    private function addMissingArrays(&$changelog, $date, $category)
    {
        if (!is_array($changelog)) {
            $changelog = array();
        }

        if (!isset($changelog[$date])) {
            $changelog[$date] = array();
        }

        if (!isset($changelog[$date][$category])) {
            $changelog[$date][$category] = array();
        }
    }

    /**
     * Sort the parsed changelog array before saving it
     *
     * @param array $changelog The parsed changelog
     */
    public static function sort(&$changelog)
    {
        uksort($changelog, function ($first, $second) {
            $a = \TimeDate::from($first);
            $b = \TimeDate::from($second);

            if ($a == $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        });
    }
}
