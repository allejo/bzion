<?php
/**
 * This file contains functionality related to interacting with the database this CMS uses
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Debug\DatabaseQuery;
use Monolog\Logger;

/**
 * Database interface class
 */
class Database
{
    /**
     * The global database connection object
     *
     * @todo Move this to the Service class
     * @var Database
     */
    private static $Database;

    /**
     * The database object used inside this class
     * @var PDO
     */
    private $dbc;

    /**
     * An instance of the logger
     * @var Logger
     */
    private $logger;

    /**
     * The id of the last row entered
     * @var int
     */
    private $last_id;

    /**
     * Create a new connection to the database
     *
     * @param string $host     The MySQL host
     * @param string $user     The MySQL user
     * @param string $password The MySQL password for the user
     * @param string $dbName   The MySQL database name
     */
    public function __construct($host, $user, $password, $dbName)
    {
        if (Service::getContainer()) {
            if ($logger = Service::getContainer()->get('monolog.logger.mysql')) {
                $this->logger = $logger;
            }
        }

        try {
            // TODO: Persist
            $this->dbc = new PDO(
                'mysql:host=' . $host . ';dbname=' . $dbName . ';charset=utf8',
                $user,
                $password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                    // We are using MySQL, so there is no need to emulate
                    // prepared statements for databases that don't support
                    // them. This line makes sure all values are returned to PHP
                    // from MySQL in the correct type, and they are not all
                    // strings.
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch (PDOException $e) {
            $this->logger->addAlert($e->getMessage());
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Destroy this connection to the database
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Get an instance of the Database object
     *
     * This should be the main way to acquire access to the database
     *
     * @todo Move this to the Service class
     *
     * @return Database The Database object
     */
    public static function getInstance()
    {
        if (!self::$Database) {
            if (Service::getEnvironment() == 'test') {
                if (!Service::getParameter('bzion.testing.enabled')) {
                    throw new Exception('You have to specify a MySQL database for testing in the bzion.testing section of your configuration file.');
                }

                self::$Database = new self(
                    Service::getParameter('bzion.testing.host'),
                    Service::getParameter('bzion.testing.username'),
                    Service::getParameter('bzion.testing.password'),
                    Service::getParameter('bzion.testing.database')
                );
            } else {
                self::$Database = new self(
                    Service::getParameter('bzion.mysql.host'),
                    Service::getParameter('bzion.mysql.username'),
                    Service::getParameter('bzion.mysql.password'),
                    Service::getParameter('bzion.mysql.database')
                );
            }
        }

        return self::$Database;
    }

    /**
     * Close the current connection to the MySQL database
     */
    public function closeConnection()
    {
        $this->dbc = null;
    }

    /**
     * Tests whether or not the connection to the database is still active
     * @todo Make this work for PDO, or deprecate it if not needed
     * @return bool True if the connection is active
     */
    public function isConnected()
    {
        return true;
    }

    /**
     * Get the unique row ID of the last row that was inserted
     * @return int The ID of the row
     */
    public function getInsertId()
    {
        return $this->last_id;
    }

    /**
     * Prepares and executes a MySQL prepared INSERT/DELETE/UPDATE statement. <em>The second parameter is optional when using this function to execute a query with no placeholders.</em>
     *
     * @param  string      $queryText The prepared SQL statement that will be executed
     * @param  mixed|array $params    (Optional) The array of values that will be binded to the prepared statement
     * @return array       Returns an array of the values received from the query
     */
    public function execute($queryText, $params = false)
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $debug = new DatabaseQuery($queryText, $params);

        $query = $this->doQuery($queryText, $params);
        $return = $query->rowCount();

        $debug->finish($return);

        return $return;
    }

    /**
     * Prepares and executes a MySQL prepared SELECT statement. <em>The second parameter is optional when using this function to execute a query with no placeholders.</em>
     *
     * @param  string      $queryText The prepared SQL statement that will be executed
     * @param  mixed|array $params    (Optional) The array of values that will be binded to the prepared statement
     * @return array       Returns an array of the values received from the query
     */
    public function query($queryText, $params = false)
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $debug = new DatabaseQuery($queryText, $params);

        $return = $this->doQuery($queryText, $params)->fetchAll();

        $debug->finish($return);

        return $return;
    }

    /**
     * Perform a query
     * @param  string      $queryText The prepared SQL statement that will be executed
     * @param  null|array  $params    (Optional) The array of values that will be binded to the prepared statement
     *
     * @return PDOStatement The PDO statement
     */
    private function doQuery($queryText, $params = null)
    {
        try {
            $query = $this->dbc->prepare($queryText);

            if ($params !== null) {
                $i = 1;
                foreach ($params as $name => $param) {
                    // Guess parameter type
                    if (is_bool($param)) {
                        $param = (int) $param;
                        $type = PDO::PARAM_INT;
                    } elseif (is_int($param)) {
                        $type = PDO::PARAM_INT;
                    } elseif (is_null($param)) {
                        $type = PDO::PARAM_NULL;
                    } elseif ($param instanceof ModelInterface) {
                        $param = (int) $param->getId();
                        $type = PDO::PARAM_INT;
                    } else {
                        $type = PDO::PARAM_STR;
                    }

                    if (is_string($name)&&0) {
                        $query->bindValue($name, $param, $type);
                    } else {
                        $query->bindValue($i++, $param, $type);
                    }
                }
            }

            $result = $query->execute();
            if ($result === false) {
                $this->error("Unknown error");
            }

            $this->last_id = $this->dbc->lastInsertId();

            return $query;
        } catch (PDOException $e) {
            $this->error($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Start a MySQL transaction
     */
    public function startTransaction()
    {
        $this->dbc->beginTransaction();
    }

    /**
     * Commit the stored queries (usable only if a transaction has been started)
     *
     * This does not show an error if there are no queries to commit
     */
    public function commit()
    {
        $this->dbc->commit();
    }

    /**
     * Cancel all pending queries (does not finish the transaction
     */
    public function rollback()
    {
        $this->dbc->rollBack();
    }

    /**
     * Commit all pending queries and finalise the transaction
     */
    public function finishTransaction()
    {
        $this->dbc->commit();
    }

    /**
     * Uses monolog to log an error message
     *
     * @param string         $error    The error string
     * @param int            $id       The error ID
     * @param Throwable|null $previous The exception that caused the error (if any)
     *
     * @throws Exception
     */
    public function error($error, $id = null, Throwable $previous = null)
    {
        if (empty($error)) {
            $error = "Unknown MySQL error - check for warnings generated by PHP";
        }

        // Create a context array so that we can log the ID, if provided
        $context = array();
        if ($id !== null) {
            $context['id'] = $id;
        }

        $this->logger->addError($error, $context);
        throw new Exception($error, (int) $id, $previous);
    }

    /**
     * Serialize the object
     *
     * Prevents PDO from being erroneously serialized
     *
     * @return array The list of properties that should be serialized
     */
    public function __sleep()
    {
        return array('last_id');
    }
}
