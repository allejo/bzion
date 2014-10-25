<?php
/**
 * This file contains scripts that are run on composer events and commands - for
 * example, you might want to regenerate the cache after updating
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Composer;

use Composer\Script\Event;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

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

    /*
     * Create and update the database schema
     *
     * @param $event Event Composer's event
     */
    public static function migrateDatabase(Event $event)
    {
        try {
            $config = self::getDatabaseConfig();
        } catch(Exception $e) {
            $event->getIO()->write("<bg=red>\n\n [WARNING] " . $e->getMessage() . ", the database won't be updated\n</>");
            return;
        }

        // If the database doesn't exist, ask the user to create it and perform
        // the necessary migrations (unless the user didn't agree to
        // create the database)
        if (self::createDatabase($event, $config['host'], $config['username'], $config['password'], $config['database'])) {
            $event->getIO()->write(''); // newline

            $arguments = array('migrate', '-e' => 'main');
            $app = new PhinxApplication('0.3.8');
            $app->run(new ArrayInput($arguments));
        }
    }

    /**
     * Create the database schema if needed
     *
     * @param string $event    Composer's event
     * @param string $host     The database host
     * @param string $username The username for the MySQL user
     * @param string $password The password for the MySQL user
     * @param string $database The name of the database
     */
    private static function createDatabase($event, $host, $username, $password, $database)
    {
        $dsn = 'mysql:host=' . $host .';charset=UTF8';
        $pdo = new \PDO($dsn, $username, $password);

        $statement = $pdo->prepare("USE `$database`");
        $status = $statement->execute();
        $errors = $statement->errorInfo();

        // Throw an exception on error for any query that will be sent next
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // 1049 is the error code thrown when the database doesn't exist, but
        // the MySQL user has the privilege to see it
        if ($errors[1] == 1049) {
            $answer = $event->getIO()->askConfirmation(
                " <fg=green>The $database database doesn't exist. Would you like to have it created? (yes/no)</> [<comment>yes</comment>]\n > ",
                true);

            if ($answer) {
                $pdo->query("CREATE DATABASE `$database` COLLATE utf8_unicode_ci");
                $pdo->query("USE `$database`");

                $event->getIO()->write(" <fg=green>New database created</>");
            } else {
                return false;
            }
        } elseif (!$status) {
            throw new \Exception("Unable to connect to database: " . $errors[2]);
        }

        // If the database is empty, fill it
        if ($pdo->query('SHOW TABLES')->rowCount() === 0) {
            $event->getIO()->write(" <fg=green>Creating database schema...</> ", false);

            $sqlPath = realpath($basepath . 'DATABASE.sql');
            $pdo->exec(file_get_contents($sqlPath));

            $event->getIO()->write("<fg=green>done.</>");
        }

        return true;
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

    /**
     * Get the database's configuration
     *
     * @return array The configuration as defined in the config.yml file
     */
    public static function getDatabaseConfig()
    {
        $basepath = __DIR__ . '/../../';

        // Read the database data from the configuration file
        $configPath = realpath($basepath . 'app') . '/config.yml';
        if (!is_file($configPath)) {
            throw new Exception("The configuration file could not be read");
        }

        $config = Yaml::parse($configPath);
        $config = $config['bzion']['mysql'];

        return $config;
    }

}
