<?php

class Visit extends Controller
{

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
     * The user agent of the visiting user
     * @var string
     */
    private $user_agent;

    /**
     * The HTTP_REFERER of the visiting user
     * @var string
     */
    private $referer;

    /**
     * The timestamp of the visit
     * @var date
     */
    private $timestamp;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "visits";

    /**
     * Construct a new Visit
     * @param int $id The visit's id
     */
    function __construct($id) {

        parent::__construct($id);
        if (!$this->valid) return;

        $visit = $this->result;

        $this->bzid = $visit['bzid'];
        $this->ip = $visit['ip'];
        $this->host = $visit['host'];
        $this->user_agent = $visit['user_agent'];
        $this->referer = $visit['referer'];
        $this->timestamp = new DateTime($visit['timestamp']);

    }

    /**
     * Enter a new visit into the database
     * @param int $bzid The visitor's bzid
     * @param string $ip The visitor's ip address
     * @param string $host The visitor's host
     * @param string $referer The HTTP_REFERER of the visit
     * @param string $timestamp The timestamp of the visit
     * @return Visit An object representing the visit that was just entered
     */
    public static function enterVisit($bzid, $ip, $host, $user_agent, $referer, $timestamp = "now") {
        $db = Database::getInstance();

        $timestamp = new DateTime($timestamp);

        $results = $db->query("INSERT INTO visits (bzid, ip, host, user_agent, referer, timestamp) VALUES (?, ?, ?, ?, ?, ?)",
        "isssss", array($bzid, $ip, $host, $user_agent, $referer, $timestamp->format(DATE_FORMAT)));


        return new Visit($db->getInsertId());
    }

}
