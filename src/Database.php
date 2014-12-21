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
     * @var Database
     */
    private static $Database;

    /**
     * The database object used inside this class
     * @var MySQLi
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
     *
     * @return Database A database object to interact with the database
     */
    public function __construct($host, $user, $password, $dbName)
    {
        if (Service::getContainer()) {
            if ($logger = Service::getContainer()->get('monolog.logger.mysql')) {
                $this->logger = $logger;
            }
        }

        $this->dbc = new mysqli($host, $user, $password, $dbName);

        if ($this->dbc->connect_errno) {
            $this->logger->addAlert($this->dbc->connect_error);
            throw new Exception($this->dbc->connect_error, $this->dbc->connect_errno);
        }

        $this->dbc->set_charset("utf8");
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
     * @return Database The Database object
     */
    public static function getInstance()
    {
        if (!self::$Database) {
            self::$Database = new Database(
                Service::getParameter('bzion.mysql.host'),
                Service::getParameter('bzion.mysql.username'),
                Service::getParameter('bzion.mysql.password'),
                Service::getParameter('bzion.mysql.database')
            );
        }

        return self::$Database;
    }

    /**
     * Close the current connection to the MySQL database
     */
    public function closeConnection()
    {
        @mysqli_close($this->dbc);
    }

    /**
     * Tests whether or not the connection to the database is still active
     * @return bool True if the connection is active
     */
    public function isConnected()
    {
        return $this->dbc->ping();
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
     * Prepares and executes a MySQL prepared statement. <em>Second two parameters are optional when using this function to execute a query with no placeholders.</em>
     *
     * <code>
     *      //the appropriate letters to show what type of variable will be passed
     *      //i - integer
     *      //d - double
     *      //s - string
     *      //b - blob
     *
     *      $database = new Database(); //create a new database object
     *
     *      $query = "SELECT * FROM table WHERE id = ?"; //write the prepared statement where ? are placeholders
     *      $params = array("1"); //all the parameters to be binded, in order
     *      $results = $database->query($query, "i", $params); //execute the prepared query
     * </code>
     *
     * @param  string      $queryText The prepared SQL statement that will be executed
     * @param  bool|string $typeDef   (Optional) The types of values that will be passed through the prepared statement. One letter per parameter
     * @param  mixed|array $params    (Optional) The array of values that will be binded to the prepared statement
     * @return mixed       Returns an array of the values received from the query or returns false on empty
     */
    public function query($queryText, $typeDef = false, $params = false)
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $debug = new DatabaseQuery($queryText, $typeDef, $params);

        $return = $this->doQuery($queryText, $typeDef, $params);

        $debug->finish($return);

        return $return;
    }

    /**
     * Perform a query
     * @param  string      $queryText The prepared SQL statement that will be executed
     * @param  bool|string $typeDef   (Optional) The types of values that will be passed through the prepared statement. One letter per parameter
     * @param  bool|array  $params    (Optional) The array of values that will be binded to the prepared statement
     * @return mixed       Returns an array of the values received from the query or returns false on empty
     */
    private function doQuery($queryText, $typeDef = false, $params = false)
    {
        $multiQuery = true;
        if ($stmt = $this->dbc->prepare($queryText)) {
            if (count($params) == count($params, 1)) {
                $params = array($params);
                $multiQuery = false;
            }

            if ($typeDef) {
                $bindParams = array();
                $bindParamsReferences = array();
                $bindParams = array_pad($bindParams, (count($params, 1) - count($params))/count($params), "");

                foreach ($bindParams as $key => $value) {
                    $bindParamsReferences[$key] = &$bindParams[$key];
                }

                array_unshift($bindParamsReferences, $typeDef);
                $bindParamsMethod = new ReflectionMethod('mysqli_stmt', 'bind_param');
                $bindParamsMethod->invokeArgs($stmt, $bindParamsReferences);
            }

            $result = array();
            foreach ($params as $queryKey => $query) {
                if ($typeDef) {
                    foreach ($bindParams as $paramKey => $value) {
                        $bindParams[$paramKey] = $query[$paramKey];
                    }
                }

                $queryResult = array();
                if ($stmt->execute()) {
                    $resultMetaData = $stmt->result_metadata();
                    $this->last_id = $stmt->insert_id;

                    if ($resultMetaData) {
                        $stmtRow = array();
                        $rowReferences = array();

                        while ($field = $resultMetaData->fetch_field()) {
                            $rowReferences[] = &$stmtRow[$field->name];
                        }

                        mysqli_free_result($resultMetaData);
                        $bindResultMethod = new ReflectionMethod('mysqli_stmt', 'bind_result');
                        $bindResultMethod->invokeArgs($stmt, $rowReferences);

                        while (mysqli_stmt_fetch($stmt)) {
                            $row = array();
                            foreach ($stmtRow as $key => $value) {
                                $row[$key] = $value;
                            }

                            $queryResult[] = $row;
                        }

                        mysqli_stmt_free_result($stmt);
                    } else {
                        $queryResult[] = mysqli_stmt_affected_rows($stmt);
                    }
                } else {
                    $this->error($this->dbc->error, $this->dbc->errno);
                    $queryResult[] = false;
                }

                $result[$queryKey] = $queryResult;
            }

            mysqli_stmt_close($stmt);
        } else {
            $result = false;
        }

        if ($this->dbc->error) {
            $this->error($this->dbc->error, $this->dbc->errno);
        }

        if ($multiQuery) {
            return $result;
        } else {
            return $result[0];
        }
    }

    /**
     * Uses monolog to log an error message
     *
     * @param string $error The error string
     * @param int    $id    The error ID
     *
     * @throws Exception
     */
    public function error($error, $id = null)
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
        throw new Exception($error, $id);
    }
}
