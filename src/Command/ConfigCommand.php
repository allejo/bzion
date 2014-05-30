<?php

namespace BZIon\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bzion:config')
            ->setDescription('Update bzion to the latest commit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<bg=green;options=bold>Welcome to the BZiON configurator!</bg=green;options=bold>');

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
