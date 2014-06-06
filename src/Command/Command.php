<?php

namespace BZIon\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Process\Process;

class Command extends ContainerAwareCommand
{
    private $progress;

    /**
     * Initialise the progress bar
     * @param  Output $output The output
     * @param  int    $count  The number of steps
     * @return void
     */
    protected function initProgress(&$output, $count)
    {
        $this->progress = new ProgressBar($output, $count);

        $this->progress->setBarCharacter('<comment>=</comment>');
        $this->progress->start();
    }

    /**
     * Advance the progress bar a step forward
     * @return void
     */
    protected function advance()
    {
        if (!$this->progress)
            return;

        $this->progress->advance();
    }

    /**
     * Mark the progress bar as finished
     * @return void
     */
    protected function finishProgress()
    {
        if (!$this->progress)
            return;

        $this->progress->finish();
    }

    protected function clearCache(&$output)
    {
        $commandOutput = ($output->isVerbose()) ? $output : new NullOutput();

        foreach (array('cache:clear', 'cache:warmup') as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $arguments = array ( 'command' => $commandName );
            $input = new ArrayInput($arguments);
            $command->run($input, $commandOutput);
            $this->advance();
        }
    }

    /**
     * Return a function that can be used by Symfony's process to show the output
     * of a process live on our screen
     * @return callable|null
     */
    protected function getBufferFunction(&$output)
    {
        if (!$output->isVerbose())
            return null;

        return function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo 'OUT > '.$buffer;
            }
        };
    }
}
