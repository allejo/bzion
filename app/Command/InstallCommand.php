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
//         $name = $input->getArgument('name');
        $output->writeln('<bg=green;options=bold>Welcome to BZiON!</bg=green;options=bold>');
        $output->writeln('<bg=red;options=bold>Note:</bg=red;options=bold> <fg=red>this script may require root access in order to properly set up permissions for the app folders</fg=red>');
        $progress = $this->getHelperSet()->get('progress');

        // the finished part of the bar
        $progress->setBarCharacter('<comment>=</comment>');
        $progress->start($output, 5);

        $git = new Process('git submodule update --init');
        $git->run();

        if (!$git->isSuccessful()) {
            throw new \RuntimeException($git->getErrorOutput());
        }
        $progress->advance();

        $webUser = new Process("ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1");
        $webUser->run();

        if (!$webUser->isSuccessful()) {
            throw new \RuntimeException($webUser->getErrorOutput());
        }
        $webUser = trim($webUser->getOutput());
        $progress->advance();

        $setPerms = new Process("sudo sh -c \"setfacl -R -m u:$webUser:rwX -m u:`whoami`:rwX app/cache app/logs && setfacl -dR -m u:$webUser:rwX -m u:`whoami`:rwX app/cache app/logs\"");
        $setPerms->run();

        if (!$setPerms->isSuccessful()) {
            throw new \RuntimeException($setPerms->getErrorOutput());
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
