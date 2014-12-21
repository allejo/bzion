<?php

namespace BZIon\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ChangesCommand extends ContainerAwareCommand
{
    /**
     * The date when the latest changes were most recently shown
     *
     * @var null|\TimeDate
     */
    private $lastUpdateDate = null;

    /**
     * An array of the most recent changelog entries that were shown to the user
     * on the last update
     *
     * Used to prevent showing the same changelog entries if the user updated
     * two times in the same day
     *
     * @var array
     */
    private $alreadyListedChanges = array();

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('bzion:changes')
            ->setDescription('List new features and bug fixes since the last update')
            ->addOption(
                'changelog',
                'c',
                InputOption::VALUE_OPTIONAL,
                'The path to the changelog file',
                dirname(dirname(__DIR__)) . '/app/changelog.yml'
            )
            ->addOption(
                'lastupdate',
                'l',
                InputOption::VALUE_OPTIONAL,
                'The path to the file containing the date of the last update',
                dirname(dirname(__DIR__)) . '/app/lastupdate.yml'
            )
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Show all the changes made since the given date, overrides the lastupdate file'
            )
            ->addOption(
                'read',
               null,
               InputOption::VALUE_NONE,
               'Mark all the changes made before the current date as read'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastUpdatePath = $input->getOption('lastupdate');
        $date           = $input->getOption('date');
        $markRead       = $input->getOption('read');
        $changelog      = Yaml::parse($input->getOption('changelog'));

        $this->parseOptions($lastUpdatePath, $date, $output);

        // Make sure the changelog dates are properly sorted (more recent to older)
        LogCommand::sort($changelog);
        $listed = $this->parseChangelog($changelog);

        if (!$markRead) {
            if ($this->isEmpty($listed)) {
                $output->writeln("No significant changes since the last update.");
            } else {
                $output->writeln("Changes since last update:");
                $this->renderChangeList($listed, $output);
            }
        }

        $this->storeLastUpdate($lastUpdatePath, $date);

        // Reset properties in case execute() is run again
        $this->lastUpdateDate = null;
        $this->alreadyListedChanges = array();
    }

    /**
     * Parse the command line options concerning the date of the last update
     *
     * @param  string          $lastUpdatePath The path to the last update file
     * @param  string          $date|null      The date command line argument
     * @param  OutputInterface $output         The command's output
     * @return void
     */
    private function parseOptions($lastUpdatePath, $date, $output)
    {
        $message = null;

        if ($date) {
            $this->lastUpdateDate = \TimeDate::from($date)->startOfDay();
        } elseif (!file_exists($lastUpdatePath)) {
            $message = "Last update file not found, a new one is going to be created";
        } else {
            $message = $this->parseLastUpdate($lastUpdatePath);
        }

        if ($output->isVeryVerbose()) {
            $output->writeln($message);

            if ($this->lastUpdateDate) {
                $formattedDate = $this->lastUpdateDate->toFormattedDateString();
                $output->writeln("Showing changes since <options=bold>$formattedDate</options=bold>");
            }

            $output->writeln("");
        }
    }

    /**
     * Parse the last update file
     *
     * @param  string $path The path to the last update file
     * @return string The message to show to the user
     */
    private function parseLastUpdate($path)
    {
        $lastUpdate = Yaml::parse($path);
        $this->lastUpdateDate = \TimeDate::from($lastUpdate['date']);
        $this->alreadyListedChanges = $lastUpdate['changes'];

        return "Using <options=bold>$path</options=bold>";
    }

    /**
     * Get a list of changes that will be shown to the user
     *
     * @param  array[] $changelog The parsed changelog.yml file
     * @return array[] The changes to show to the user
     */
    private function parseChangelog($changelog)
    {
        $listed = array();
        $firstEntry = true;
        $lastChangeDate = \TimeDate::now()->startOfDay();
        $lastChanges = array();

        foreach ($changelog as $date => $changes) {
            $date = \TimeDate::from($date);

            if ($firstEntry) {
                // The array has been sorted, the first entry represents the
                // most recent change. Store its date so that we don't show the
                // same entry many times
                $firstEntry = false;

                if ($lastChangeDate >= $date) {
                    $lastChangeDate = $date;
                    $lastChanges = $changes;
                }
            }

            // Don't list changes that we've listed before
            if ($date == $this->lastUpdateDate) {
                $this->filterAlreadyListedChanges($changes);
            } elseif ($this->lastUpdateDate && $date < $this->lastUpdateDate) {
                break;
            }

            $listed = array_merge_recursive($listed, $changes);
        }

        $this->alreadyListedChanges = $lastChanges;
        $this->lastUpdateDate = $lastChangeDate;

        return $listed;
    }

    /**
     * Filter out the changes made today that have already been shown to the
     * user
     * @param  array $changes Today's changes
     * @return void
     */
    private function filterAlreadyListedChanges(&$changes)
    {
        $alreadyListed = $this->alreadyListedChanges;

        foreach ($changes as $type => &$changelist) {
            $changelist = array_filter($changelist, function ($change) use ($type, $alreadyListed) {
                if (!isset($alreadyListed[$type])) {
                    return true;
                }

                return !in_array($change, $alreadyListed[$type]);
            });
        }
    }

    /**
     * Show a list of changes in a user-readable format
     *
     * @param  array[]         $listed The changes that should be listed
     * @param  OutputInterface $output The command's output
     * @return void
     */
    private function renderChangeList($listed, OutputInterface $output)
    {
        $types = array(
            'Features' => '<info>[+] %s</info>',
            'Bugfixes' => '<comment>[*] %s</comment>',
            'Notes'    => '<bg=red;options=bold>[!] %s</bg=red;options=bold>',
        );

        foreach ($types as $type => $format) {
            if (isset($listed[$type])) {
                foreach ($listed[$type] as $change) {
                    $output->writeln(sprintf($format, $change));
                }
            }
        }
    }

    /**
     * Store the newest entry's date into the last update file, so that the user
     * isn't shown the same changes in the future
     *
     * @param  string  $path The path to the last update file
     * @param  boolean $date The date command line argument (used to determine
     *                       whether we should store the last update or not)
     * @return void
     */
    private function storeLastUpdate($path, $date)
    {
        if ($date !== null) {
            // The user probably run the command to see old changes, we don't
            // consider this a result of an update
            return;
        }

        $data = array(
            'date'    => $this->lastUpdateDate->toFormattedDateString(),
            'changes' => $this->alreadyListedChanges
        );

        file_put_contents($path, Yaml::dump($data, 3));
    }

    /**
     * Recursively find out if an array is empty
     *
     * @param  array   $array The array to test
     * @return boolean|null
     */
    private function isEmpty(array $array)
    {
        if (empty($array)) {
            return true;
        }

        foreach ($array as $child) {
            if (!is_array($child)) {
                return false;
            }

            return self::isEmpty($child);
        }
    }
}
