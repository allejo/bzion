<?php

namespace Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Process\Process;

class UpdateCommand extends ContainerAwareCommand
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
        $progress = $this->getHelperSet()->get('progress');

        // the finished part of the bar
        $progress->setBarCharacter('<comment>=</comment>');
        $progress->start($output, 7);

        if (file_exists('composer.phar')) {
            $composerLocation = 'php composer.phar';
        } else {
            $composerLocation = 'composer';
        }

        $commands = array(
                    "git submodule update --init",
                    "git stash", // Save any changes that have been made so
                                 // that git doesn't complain
                    "git pull origin master",
                    "git stash pop",
                    "$composerLocation install --no-dev"
                    );

        foreach ($commands as $cn => $c) {
            $process = new Process($c);
            $process->run();

            if (!$process->isSuccessful()) {
                if ($cn == 2) {
                    // Pull failed, let's pop what we've stashed
                    $pop = new Process($commands[3]);
                    $pop->run();
                }

                throw new \RuntimeException($process->getErrorOutput());
            }
            $progress->advance();
        }

        foreach (array('cache:clear', 'cache:warmup') as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $arguments = array ( 'command' => $commandName );
            $input = new ArrayInput($arguments);
            $clearReturn = $command->run($input, new NullOutput());
            $progress->advance();
        }

        $progress->finish();
    }
}
