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

class ConfigCommand extends ContainerAwareCommand
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
        $output->writeln('<bg=green;options=bold>Welcome to the BZiON congifurator!</bg=green;options=bold>');

        $dialog = $this->getHelperSet()->get('dialog');

        $host = $dialog->ask(
            $output,
            '<question>Database Host</question>: ',
            '127.0.0.1',
            array('127.0.0.1', 'localhost')
        );

        $database = $dialog->ask(
            $output,
            '<question>Database Name</question>: ',
            'bzion',
            array('bzion', 'league')
        );

        $user = $dialog->ask(
            $output,
            '<question>Database User</question>: ',
            'bzion',
            array('bzion', 'league', 'root')
        );

        $pass = $dialog->askHiddenResponse(
            $output,
            '<question>Database Password</question>: ',
            false
        );

        $prod = $dialog->select(
            $output,
            '<question>Environment</question>: ',
            array("Development", "Production"),
            true
        );




        $output->writeln('<fg=green;options=bold>BZiON has been updated successfully!</fg=green;options=bold>');
    }
}
