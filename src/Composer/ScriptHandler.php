<?php
/**
 * This file contains scripts that are run on composer events and commands - for
 * example, you might want to regenerate the cache after updating
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Composer;

use Composer\IO\ConsoleIO;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
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
     * Initialize the last update file so that when the user updates and asks
     * for the changelog, the entries added before the installation are not shown
     *
     * @param $event Event Composer's event
     */
    public static function initializeChangelog(Event $event)
    {
        static::executeCommand($event, 'bzion:changes --read');
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
     * @param $event   Event|null Composer's event
     * @param $testing boolean    Whether to migrate the testing database (only applicable when $event is null)
     */
    public static function migrateDatabase(Event $event = null, $testing = false)
    {
        if ($event) {
            // Use the event's IO
            $io = $event->getIO();

            $arguments = $event->getArguments();

            $testingArguments = array('testing', '--testing', '-t');
            $testing = count(array_intersect($arguments, $testingArguments)) > 0;
        } else {
            // Create our own IO
            $input = new ArrayInput(array());
            $output = new ConsoleOutput();
            $helperSet = new HelperSet(array(new QuestionHelper()));

            $io = new ConsoleIO($input, $output, $helperSet);
        }

        try {
            $config = self::getDatabaseConfig($testing);
        } catch (\Exception $e) {
            $io->write("<bg=red>\n\n [WARNING] " . $e->getMessage() . ", the database won't be updated\n</>");

            return;
        }

        // If the database doesn't exist, ask the user to create it and perform
        // the necessary migrations (unless the user didn't agree to
        // create the database)
        if (self::createDatabase($io, $config['host'], $config['username'], $config['password'], $config['database'])) {
            $io->write(''); // newline

            $arguments = array('migrate', '-e' => ($testing) ? 'test' : 'main');
            $app = new PhinxApplication();
            $app->doRun(new ArrayInput($arguments), new ConsoleOutput());
        }
    }

    /**
     * Shows an installation success message
     *
     * @param $event Event Composer's event
     */
    public static function showSuccessMessage(Event $event)
    {
        static::executeCommand($event, 'bzion:success');
    }

    /**
     * Create the database schema if needed
     *
     * @param IOInterface $io       Composer's IO interface
     * @param string      $host     The database host
     * @param string      $username The username for the MySQL user
     * @param string      $password The password for the MySQL user
     * @param string      $database The name of the database
     */
    private static function createDatabase(IOInterface $io, $host, $username, $password, $database)
    {
        $io->write(" Connecting to MySQL database $database@$host");

        $dsn = 'mysql:host=' . $host . ';charset=UTF8';
        $pdo = new \PDO($dsn, $username, $password);

        $statement = $pdo->prepare("USE `$database`");
        $status = $statement->execute();
        $errors = $statement->errorInfo();

        // Throw an exception on error for any query that will be sent next
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // 1049 is the error code thrown when the database doesn't exist, but
        // the MySQL user has the privilege to see it
        if ($errors[1] == 1049) {
            $answer = $io->askConfirmation(
                " <fg=green>The $database database doesn't exist. Would you like to have it created? (yes/no)</> [<comment>yes</comment>]\n > ",
                true);

            if ($answer) {
                $pdo->query("CREATE DATABASE `$database` COLLATE utf8_unicode_ci");
                $pdo->query("USE `$database`");

                $io->write(" <fg=green>New database created</>");
            } else {
                return false;
            }
        } elseif (!$status) {
            throw new \Exception("Unable to connect to database: " . $errors[2]);
        }

        // If the database is empty, fill it
        if ($pdo->query('SHOW TABLES')->rowCount() === 0) {
            $io->write(" <fg=green>Creating database schema...</> ", false);

            $sqlPath = realpath(__DIR__ . '/../../migrations/' . 'DATABASE.sql');
            $pdo->exec(file_get_contents($sqlPath));

            $io->write("<fg=green>done.</>");
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
        $console = escapeshellarg(__DIR__ . '/../../app/console') . ' --env=prod';

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
     * @param  boolean    $testing Whether to retrieve the test database credentials
     * @return array|null The configuration as defined in the config.yml file, null if no configuration was found
     */
    public static function getDatabaseConfig($testing = false)
    {
        $configPath = ConfigHandler::getConfigurationPath();
        if (!is_file($configPath)) {
            throw new \Exception("The configuration file could not be read");
        }

        $path = $testing ? 'testing' : 'mysql';

        $config = Yaml::parse(file_get_contents($configPath));

        if (isset($config['bzion'][$path])) {
            return $config['bzion'][$path];
        }

        return null;
    }
}
