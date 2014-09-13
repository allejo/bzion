<?php
/**
 * This file contains scripts that are run on composer events and commands - for
 * example, you might want to regenerate the cache after updating
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Composer;

use Symfony\Component\Process\Process;
use Composer\Script\CommandEvent;

/**
 * A manager for composer events
 */
class ScriptHandler
{
    /**
    * Clears the Symfony cache.
    *
    * @param $event CommandEvent Composer's event
    */
    public static function clearCache(CommandEvent $event)
    {
        static::executeCommand($event, 'cache:clear');
    }

    /**
     * Execute a symfony console command
     *
     * @param  CommandEvent Composer's event
     * @param  string $command The command to execute
     * @param  int $timeout The timeout of the command in seconds
     * @return void
     */
    protected static function executeCommand(CommandEvent $event, $command, $timeout = 300)
    {
        $console = escapeshellarg(__DIR__ .'/../../app/console');

        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }

        $process = new Process("$console $command", null, null, null, $timeout);
        $process->run(function ($type, $buffer) use ($event) { $event->getIO()->write($buffer, false); });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($command)));
        }
    }

}
