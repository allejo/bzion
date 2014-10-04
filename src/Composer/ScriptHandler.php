<?php
/**
 * This file contains scripts that are run on composer events and commands - for
 * example, you might want to regenerate the cache after updating
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Composer;

use Symfony\Component\Process\Process;
use Composer\Script\Event;

/**
 * A manager for composer events
 */
class ScriptHandler
{
    /**
     * Clears the Symfony cache.
     *
     * @param $event Event Composer's event
     */
    public static function clearCache(Event $event)
    {
        static::executeCommand($event, 'cache:clear');
    }

    /**
     * Shows what changed since the last update
     *
     * @param $event Event Composer's event
     */
    public static function showChangelog(Event $event)
    {
        static::executeCommand($event, 'bzion:changes');
    }

    /**
     * Migrate the config.yml file
     *
     * @param $event Event Composer's event
     */
    public static function buildConfig(Event $event)
    {
        $configHandler = new ConfigHandler($event);
        $configHandler->build();
    }

    /**
     * Execute a symfony console command
     *
     * @param  Event Composer's event
     * @param  string $command The command to execute
     * @param  int    $timeout The timeout of the command in seconds
     * @return void
     */
    protected static function executeCommand(Event $event, $command, $timeout = 300)
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
