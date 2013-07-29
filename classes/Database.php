<?php

class Database
{
    /**
     * The database object used inside this class
     * @var mixed
     */
    private $dbc;

    /**
     * The id of the last row entered
     * @var mixed
     */
    private $last_id;

    /**
     * Create a new connection to the database
     * @return Database
     */
    function __construct()
    {
        $this->dbc = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB_NAME);

        if ($this->dbc->connect_errno)
            echo "Something went wrong with the database connection.";
        else
            $this->dbc->set_charset("utf8");
    }

    function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Get an instance of the Database object
     *
     * This should be the main way to acquire access
     * to the database
     * @return Database The Database object
     */
    static function getInstance() {
        return $GLOBALS['db'];
    }

    /**
     * Close the current connection to the MySQL database
     */
    function closeConnection()
    {
        mysqli_close($this->dbc);
    }

    /**
     * Tests whether or not the connection to the database is still active
     * @return bool True if the connection is active
     */
    function isConnected()
    {
        return $this->dbc->ping();
    }

    /**
     * Get the unique row ID of the last row that was inserted
     * @return int The ID of the row
     */
    function getInsertId()
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
     * @param string $query The prepared SQL statement that will be executed
     * @param string $typeDef (Optional) The types of values that will be passed through the prepared statement. One letter per parameter
     * @param array $params (Optional) The array of values that will be binded to the prepared statement
     * @return mixed Returns an array of the values received from the query or returns false on empty
     */
    function query($query, $typeDef = FALSE, $params = FALSE)
    {
        if ($stmt = $this->dbc->prepare($query))
        {
            if (count($params) == count($params, 1))
            {
                $params = array($params);
                $multiQuery = false;
            }
            else
                $multiQuery = true;

            if ($typeDef)
            {
                $bindParams = array();
                $bindParamsReferences = array();
                $bindParams = array_pad($bindParams, (count($params, 1) - count($params))/count($params), "");

                foreach ($bindParams as $key => $value)
                    $bindParamsReferences[$key] = &$bindParams[$key];

                array_unshift($bindParamsReferences, $typeDef);
                $bindParamsMethod = new ReflectionMethod('mysqli_stmt', 'bind_param');
                $bindParamsMethod->invokeArgs($stmt, $bindParamsReferences);
            }

            $result = array();
            foreach ($params as $queryKey => $query)
            {
                foreach ($bindParams as $paramKey => $value)
                    $bindParams[$paramKey] = $query[$paramKey];

                $queryResult = array();
                if ($stmt->execute())
                {
                    $resultMetaData = $stmt->result_metadata();
                    $this->last_id = $stmt->insert_id;

                    if ($resultMetaData)
                    {
                        $stmtRow = array();
                        $rowReferences = array();

                        while ($field = $resultMetaData->fetch_field())
                        {
                            $rowReferences[] = &$stmtRow[$field->name];
                        }

                        mysqli_free_result($resultMetaData);
                        $bindResultMethod = new ReflectionMethod('mysqli_stmt', 'bind_result');
                        $bindResultMethod->invokeArgs($stmt, $rowReferences);

                        while (mysqli_stmt_fetch($stmt))
                        {
                            $row = array();
                            foreach ($stmtRow as $key => $value)
                            {
                                $row[$key] = $value;
                            }

                            $queryResult[] = $row;
                        }

                        mysqli_stmt_free_result($stmt);
                    }
                    else
                        $queryResult[] = mysqli_stmt_affected_rows($stmt);
                }
                else
                    $queryResult[] = false;

                $result[$queryKey] = $queryResult;
            }

            mysqli_stmt_close($stmt);
        }
        else
            $result = false;

        if ($this->db->error)
            writeToDebug("MySQL Error :: " . $this->db->error);

        if ($multiQuery)
            return $result;
        else
            return $result[0];
    }

    /**
    * Writes the specified string to the log file if logging is enabled
    * @param The string that will written
    */
    function writeToDebug($string)
    {
        if (MYSQL_DEBUG)
        {
            $file_handler = fopen(ERROR_LOG, 'a');
            fwrite($file_handler, date("Y-m-d H:i:s") . " :: " . $string . "\n");
            fclose($file_handler);
        }
    }
}
