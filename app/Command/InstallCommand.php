<?php

namespace Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
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

        $this->clearCache($progress);

        $progress->finish();
    }
}
