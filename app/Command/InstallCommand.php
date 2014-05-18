<?php

namespace Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Process\Process;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bzion:install')
            ->setDescription('Install bzion')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<bg=green;options=bold>Welcome to BZiON!</bg=green;options=bold>');
        $progress = $this->getHelperSet()->get('progress');

        $progress->setBarCharacter('<comment>=</comment>');
        $progress->start($output, 3);

        $git = new Process('git submodule update --init');
        $git->run();

        if (!$git->isSuccessful()) {
            throw new \RuntimeException($git->getErrorOutput());
        }
        $progress->advance();

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
