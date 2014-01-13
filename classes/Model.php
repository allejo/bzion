<?php

abstract class Model {

    /**
     * The Database ID of the object
     * @var int
     */
    protected $id;

    /**
     * A unique URL-friendly identifier for the object
     * @var string
     */
    protected $alias;

    /**
     * The name of the database table used for queries
     * @var string
     */
    protected $table;

    /**
     * False if there isn't any row in the database representing
     * the requested object ID
     * @var boolean
     */
    protected $valid;

    /**
     * The result of the database query to locate the object with a specific ID
     * @var array
     */
    protected $result;

    /**
     * The database variable used for queries
     * @var Database
     */
    protected $db;

    /**
     * The name of the database table used for queries
     * You can use this constant in static functions as such:
     * static::TABLE
     */
    const TABLE = "";

    /**
     * Construct a new Model
     *
     * This method takes the ID of the object to look for and creates a
     * $this->db object which can be used to communicate with the database,
     * as well as a $this->result array which is the single result of the
     * $this->db->query function. If the $id is specified as 0, then an
     * invalid object will be returned
     *
     * @param int $id The ID of the object to look for
     * @param string $column The column to use to identify separate database entries
     */
    function __construct($id, $column = "id") {
        if ($id == 0) {
            $this->valid  = false;
            $this->result = array();
            return;
        }

        $this->db = Database::getInstance();

        if ($column == "id")
            $this->id = $id;
        $this->table = static::TABLE;

        $results = $this->db->query("SELECT * FROM " . $this->table . " WHERE " . $column . " = ?", "i", array($id));

        if (count($results) < 1) {
            $this->valid  = false;
            $this->result = array();
        } else {
            $this->valid  = true;
            $this->result = $results[0];
        }
    }

    /**
     * Update a database field
     * @param string $name The name of the column
     * @param mixed $value The value to set the column to
     * @param string $type The type of the value, can be 's' (string), 'i' (integer), 'd' (double), 'b' (blob) or nothing to let the function guess it
     */
    public function update($name, $value, $type=NULL) {
        $this->db->query("UPDATE ". static::TABLE . " SET $name = ? WHERE id = ?", $type."i", array($value, $this->id));
    }

    /**
     * Delete the object
     *
     * Please note that this does not delete the object entirely from the database,
     * it only hides it from users. You should overload this function if your object
     * does not have a 'status' column which can be set to 'deleted'.
     */
    public function delete() {
        $this->__set('status', 'deleted');
    }

    /**
     * Permanently delete the object from the database
     */
    public function wipe() {
        $this->db->query("DELETE FROM " . $this->table . " WHERE id = ?", "i", array($this->id));
    }

    /**
     * Get an object's database ID
     * @return int The ID
     */
    public function getId() {
        return $this->id;
    }

    /**
     * See if an object is valid
     * @return bool
     */
    public function isValid() {
        return $this->valid;
    }

    /**
     * Get a URL that points to an object's page
     * @param string $dir The virtual directory the URL should point to
     * @param string $default The value that should be used if the alias is NULL. The object's ID will be used if a default value is not specified
     * @return string
     */
    protected function getURL($dir="", $default = null) {
        if (!empty($dir)) {
            $dir .= "/";
        }
        if (isset($this->alias) && $this->alias) {
            $alias = $this->alias;
        } else if (!$default) {
            $alias = $this->id;
        } else {
            $alias = $default;
        }

        $url = BASE_URL . '/' . $dir . $alias;
        return $url;
    }

    /**
     * Get a permanent URL that points to an object's page
     * @param string $dir The virtual directory the URL should point to
     * @return string
     */
    protected function getPermaLink($dir="") {
        if (!empty($dir)) {
            $dir .= "/";
        }

        $url = BASE_URL . '/' . $dir . $this->id;
        return $url;
    }

    /**
     * Gets one object's id from the supplied alias
     * @param string $value The value which the column should be equal to
     * @param string $column The name of the database column
     * @param bool $bzid Whether the function should return a BZID instead of an ID
     * @return int The ID of the object
     */
    protected static function getIdFrom($value, $column, $bzid=false) {
        $bz = ($bzid) ? "bz" : "";
        $db = Database::getInstance();
        $results = $db->query("SELECT " . $bz . "id FROM " . static::TABLE . " WHERE " . $column . "=?", "s", array($value));

        if(!isset($results[0]))
            return 0;
        return $results[0][$bz.'id'];
    }

    /**
     * Gets an array of object IDs
     * @todo Make this PHPDoc message easier to understand
     * @param string $select The name of the column(s) that the returned array should contain
     * @param string $additional_params Additional parameters to be paseed to the MySQL query (e.g. `WHERE id = 5`)
     * @param string $types The types of values that will be passed to Database::query()
     * @param array $params The parameter values that will be passed to Database::query()
     * @return array A list of values, if $select was only one column, or the return array of $db->query if it was more
     */
    protected static function getIds($select='id', $additional_params='', $types='', $params=array(), $table = "") {
        $table = (empty($table)) ? static::TABLE : $table;
        $db = Database::getInstance();
        if (empty($additional_params)) {
            $results = $db->query("SELECT " . $select . " FROM " . $table . " WHERE 1 = ?", "i", array(1));
        } else {
            if (empty($types)) {
                $results = $db->query("SELECT $select FROM " . $table . " $additional_params");
            }
            $results = $db->query("SELECT $select FROM " . $table . " $additional_params", $types, $params);
        }

        // If $select specifies multiple columns, just return the $results array
        if (isset($results[0]) && count($results[0]) != 1) {
            return $results;
        }

        $ids = array();

        // Find the correct value if the user specified a table
        // For example, if $select is "groups.id", we should convert it to
        // "id", because that's how MySQLi stores column names in the $results
        // array.
        $selectArray = explode(".",$select);
        $select = end($selectArray);

        foreach ($results as $r) {
            $ids[] = $r[$select];
        }
        return $ids;
    }

    /**
     * Gets an array of object IDs that have a column equal to something else
     * @param string $column The name of the column that should be tested
     * @param array $possible_values List of acceptable values
     * @param bool $negate Whether to search if the value of $column does NOT belong to the $possible_values array
     * @param string $type The type of the values in $possible_values (can be `s`, `i`, `d` or `b`)
     * @param string $select The name of the column(s) that the returned array should contain
     * @return array A list of values, if $select was only one column, or the return array of $db->query if it was more
     */
    protected static function getIdsFrom($column, $possible_values, $type, $negate=false, $select='id', $additional_params='', $table = "") {
        $table = (empty($table)) ? static::TABLE : $table;
        $conditions = array();
        $types = "";
        $equality = "=";
        $conjunction = "OR";
        if ($negate) {
            $equality = "!=";
            $conjunction = "AND";
        }

        foreach ($possible_values as $p) {
            $conditions[] = "$column $equality ?";
            $types .= $type;
        }
        $conditionString = "WHERE " . implode(" $conjunction ", $conditions);

        if (!empty($additional_params)) {
            $conditionString .= " " . $additional_params;
        }

        return self::getIds($select, $conditionString, $types, $possible_values, $table);
    }

    /**
     * Generate an invalid object
     *
     * <code>
     *     <?php
     *     $object = Team::invalid();
     *
     *     get_class($object); // Team
     *     $object->isValid(); // false
     * </code>
     * @return Model
     */
    public static function invalid() {
        return new static(0);
    }

    /**
     * Generate a URL-friendly unique alias for an object name
     *
     * @param string $name The original object name
     * @return string|Null The generated alias, or Null if we couldn't make one
     */
    static function generateAlias($name) {
        // Convert name to lowercase
        $name = strtolower($name);

        // List of characters which should be converted to dashes
        $makeDash = array(' ', '_');

        $name = str_replace($makeDash, '-', $name);

        // Only keep letters, numbers and dashes - delete everything else
        $name = preg_replace("/[^a-zA-Z\-0-9]+/", "", $name);

        if (str_replace('-', '', $name) == '') {
            // The name only contains symbols or Unicode characters!
            // This means we can't convert it to an alias
            return null;
        }

        // An alias name can't only contain numbers, because it will be
        // indistinguishable from an ID. If it does, add a dash in the end.
        if (preg_match("/^[0-9]+$/", $name)) {
            $name = $name . '-';
        }

        // Try to find duplicates
        $db = Database::getInstance();
        $result = $db->query("SELECT alias FROM " . static::TABLE . " WHERE alias REGEXP ?", 's', array("^" . $name . "[0-9]*$"));

        // The functionality of the following code block is provided in PHP 5.5's
        // array_column function. What is does is convert the multi-dimensional
        // array that $db->query() gave us into a single-dimensional one.
        $aliases = array();
        if (is_array($result)) {
            foreach ($result as $r) {
                $aliases[] = $r['alias'];
            }
        }

        // No duplicates found
        if (!in_array($name, $aliases))
            return $name;

        // If there's already an entry with the alias we generated, put a number
        // in the end of it and keep incrementing it until there is we find
        // an open spot.
        $i = 2;
        while (in_array($name . $i, $aliases)) {
            $i++;
        }

        return $name . $i;
    }

}
