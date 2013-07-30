<?php

abstract class Controller {

    /**
     * The Database ID of the object
     * @var int
     */
    protected $id;

    /**
     * The name of the database table used for queries
     * @var Database
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
    function __construct($id, $table=null, $column="id") {

        $this->db = Database::getInstance();

        if ($column == "id") $this->id = $id;
        $this->table = $table;

        $results = $this->db->query("SELECT * FROM " . $table . " WHERE " . $column . " = ?", "i", array($id));
        $this->result = $results[0];
    }

}
