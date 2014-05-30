<?php

namespace BZIon\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class UpdateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('bzion:update')
            ->setDescription('Update bzion to the latest commit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<bg=green;options=bold>Welcome to the BZiON updater!</bg=green;options=bold>');

        if (!$output->isVerbose())
            $this->initProgress($output, 9);

        $changeCount = $this->countGitChanges();
        $this->advance();

        $commands = array(
                    "git stash", // Save any changes that have been made so
                                 // that git doesn't complain
                    "git pull origin",
                    "git submodule sync",
                    "git submodule update --init",
                    "git stash pop",
                    $this->getComposerCommand()
                    );

        if ($changeCount < 1) {
            // Nothing has changed, no need to run git stash
            $commands[0] = $commands[4] = null;
        }

        foreach ($commands as $cn => $c) {
            $process = new Process($c);
            $process->run($this->getBufferFunction($output));

            if (!$process->isSuccessful()) {
                if ($cn == 2) {
                    // Pull failed, let's pop what we've stashed
                    $pop = new Process($commands[3]);
                    $pop->run();
                }

                throw new \RuntimeException($process->getErrorOutput());
            }
            $this->advance();
        }

        $this->clearCache($output);
        $this->finishProgress();

        $output->writeln('<fg=green;options=bold>BZiON has been updated successfully!</fg=green;options=bold>');
    }

    private function countGitChanges()
    {
        // Get number of changes to see if we need to stash anything
        $changeCount = new Process("git status --porcelain --untracked-files=no");
        $changeCount->run();
        if (!$changeCount->isSuccessful())
            throw new \RuntimeException($process->getErrorOutput());

        return substr_count( $changeCount->getOutput(), "\n" );
    }

    private function getComposerCommand()
    {
        $composerLocation = (file_exists('composer.phar'))
                          ? 'php composer.phar'
                          : 'composer';

        $arguments = "";
        if (in_array($this->getContainer()->get('kernel')->getEnvironment(), array('prod', 'dev')))
            $arguments .= " --no-dev";

        return "$composerLocation install $arguments";
    }
}
