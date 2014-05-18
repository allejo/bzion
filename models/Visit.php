<?php
/**
 * This file contains functionality to keep track of visitor sessions of registered users
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A player's visit on the website
 * @package    BZiON\Models
 */
class Visit extends Model
{

    /**
     * The id of the visiting user
     * @var int
     */
    private $player;

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
     * @var DateTime
     */
    private $timestamp;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "visits";

    /**
     * Construct a new Visit
     * @param int $id The visitor's id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        if (!$this->valid) return;

        $visit = $this->result;

        $this->player = $visit['player'];
        $this->ip = $visit['ip'];
        $this->host = $visit['host'];
        $this->user_agent = $visit['user_agent'];
        $this->referer = $visit['referer'];
        $this->timestamp = new DateTime($visit['timestamp']);

    }

    /**
     * Enter a new visit into the database
     * @param  int    $visitor    The visitor's id
     * @param  string $ip         The visitor's ip address
     * @param  string $host       The visitor's host
     * @param  string $user_agent The visitor's user agent
     * @param  string $referrer   The HTTP_REFERRER of the visit
     * @param  string $timestamp  The timestamp of the visit
     * @return Visit  An object representing the visit that was just entered
     */
    public static function enterVisit($visitor, $ip, $host, $user_agent, $referrer, $timestamp = "now")
    {
        $db = Database::getInstance();

        $timestamp = new DateTime($timestamp);

        $db->query("INSERT INTO visits (player, ip, host, user_agent, referer, timestamp) VALUES (?, ?, ?, ?, ?, ?)",
        "isssss", array($visitor, $ip, $host, $user_agent, $referrer, $timestamp->format(DATE_FORMAT)));

        return new Visit($db->getInsertId());
    }

}
