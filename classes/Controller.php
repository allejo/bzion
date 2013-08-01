<?php

abstract class Controller {

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
     * Construct a new Controller
     *
     * This method takes the table and ID of the object to look for, and
     * creates a $this->db object which can be used to communicate with the
     * database, as well as a $this->result array which is the single result
     * of the $this->db->query function
     *
     * @param int $id The ID of the object to look for
     * @param int $table The name of the DB table used for queries
     * @param string $column The column to use to identify separate database entries
     */
    function __construct($id, $column = "id") {

        $this->db = Database::getInstance();

        if ($column == "id")
            $this->id = $id;
        $this->table = static::TABLE;

        $results = $this->db->query("SELECT * FROM " . $this->table . " WHERE " . $column . " = ?", "i", array($id));
        $this->result = $results[0];
    }

    /**
     * Update a database field
     * @param string $name The name of the column
     * @param mixed $value The value to set the column to
     * @param string $type The type of the value, can be 's' (string), 'i' (integer), 'd' (double), 'b' (blob) or nothing to let the function guess it
     */
    public function update($name, $value, $type=NULL) {
        if (!$type) {
            if (is_int($value))
                $type = 'i';
            else if (is_float($value))
                $type = 'd';
            else if (is_array($value)) {
                $type = 's';
                $value = serialize($value);
            } else {
                $type = 's';
            }
        }

        $this->db->query("UPDATE ". $this->table . " SET " . $name . " = ? WHERE id = ?", $type."i", array($value, $this->id));
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

    /*
     * Get a URL that points to an object's page
     * @param string $dir The virtual directory the URL should point to
     * @param string $default The value that should be used if the alias is NULL. The object's ID will be used if a default value is not specified
     * @return string 
     */
    protected function getURL($dir, $default = null) {
        if (isset($this->alias) && $this->alias) {
            $alias = $this->alias;
        } else if (!$default) {
            $alias = $this->id;
        } else {
            $alias = $default;
        }
        
        $url = "http://" . rtrim(HTTP_ROOT, '/') . '/' . $dir . '/' . $alias;
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
        return $results[0][$bz.'id'];
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
