<?php
/**
 * This file contains functionality to keep track of visitor sessions of registered users
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */
use BZIon\Model\Column\Timestamp;

/**
 * A player's visit on the website
 * @package    BZiON\Models
 */
class Visit extends Model
{
    use Timestamp;

    /**
     * The id of the visiting user
     * @var int
     */
    protected $player;

    /**
     * The ip of the visiting user
     * @var string
     */
    protected $ip;

    /**
     * The host of the visiting user
     * @var string
     */
    protected $host;

    /**
     * The user agent of the visiting user
     * @var string
     */
    protected $user_agent;

    /**
     * The HTTP_REFERER of the visiting user
     * @var string
     */
    protected $referer;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "visits";

    /**
     * {@inheritdoc}
     */
    protected function assignResult($visit)
    {
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
        $timestamp = TimeDate::from($timestamp);

        return self::create(array(
            'player'     => $visitor,
            'ip'         => $ip,
            'host'       => $host,
            'user_agent' => $user_agent,
            'referer'    => $referrer,
            'timestamp'  => $timestamp->toMysql(),
        ));
    }
}
