<?php

class Ban {

    /**
     * The id of the visiting user
     * @var int
     */
    private $id;

    /**
     * The bzid of the visiting user
     * @var int
     */
    private $bzid;

    /**
     * The ip of the visiting user
     * @var string
     */
    private $ip;

    /**
     * The host of the visiting user
     * @var string
     */
    private $host;

    /**
     * The HTTP_REFERER of the visiting user
     * @var string
     */
    private $referer;

    /**
     * The date of the visit
     * @var date
     */
    private $timestamp;

    /**
     * The database variable used for queries
     * @var Database
     */
    private $db;

    /**
     * Construct a new Visit
     * @param int $id The visit's id
     */
    function __construct($id) {

        $this->db = Database::getInstance();
        $this->id = $id;

        $results = $this->db->query("SELECT * FROM visits WHERE id = ?", "i", array($id));
        $visit = $results[0];

        $this->bzid = $visit['bzid'];
        $this->ip = $visit['ip'];
        $this->host = $visit['host'];
        $this->referer = $visit['referer'];
        $this->timestamp = new DateTime($visit['timestamp']);

    }

    /**
     * Overload __set to update instance variables and database
     * @param string $name The variable's name
     * @param mixed $value The variable's new value
     */
    function __set($name, $value)
    {
        $table = "visits";

        if ($name == 'bzid') {
            $type = 'i';
        } else if ($name == 'ip' || $name == 'host' || $name == 'referer' ||
                   $name == 'timestamp') {
            $type = 's';
        }

        if (isset($type)) {
            $this->db->query("UPDATE ". $table . " SET " . $name . " = ? WHERE id = ?", $type."i", array($value, $this->id));
            $this->{$name} = $value;
        }

    }

    /**
     * Enter a new visit to the database
     * @param int $bzid The visitor's bzid
     * @param int $ip The visitor's ip address
     * @param int $host The visitor's host
     * @param int $referer The HTTP_REFERER of the visit
     * @param string $timestamp The timestamp of the visit
     * @return Visit An object representing the visit that was just entered
     */
    public static function enterVisit($bzid, $ip, $host, $referer, $timestamp = "now") {
        $db = Database::getInstance();

        $timestamp = new DateTime($timestamp);

        $results = $db->query("INSERT INTO visits (bzid, ip, host, referer, timestamp) VALUES (?, ?, ?, ?, ?)",
        "issss", array($bzid, $ip, $host, $referer, $timestamp->format('Y-m-d H:i:s')));


        return new Visit($db->getInsertId());
    }

}
