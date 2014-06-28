<?php
/**
 * This file contains functionality relating to the official BZFlag match servers for the league
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

include_once(DOC_ROOT . "/includes/bzfquery.php");

/**
 * A BZFlag server
 * @package    BZiON\Models
 */
class Server extends Model
{

    /**
     * The name of the server
     * @var string
     */
    protected $name;

    /**
     * The address of the server
     * @var string
     */
    protected $address;

    /**
     * The id of the owner of the server
     * @var int
     */
    protected $owner;

    /**
     * The server's bzfquery information
     * @var array
     */
    protected $info;

    /**
     * The date of the last bzfquery of the server
     * @var TimeDate
     */
    protected $updated;

    /**
     * The server's status
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "servers";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($server)
    {
        $this->name = $server['name'];
        $this->address = $server['address'];
        $this->owner = $server['owner'];
        $this->info = unserialize($server['info']);
        $this->updated = new TimeDate($server['updated']);
    }

    /**
     * Add a new server
     *
     * @param  string $name    The name of the server
     * @param  string $address The address of the server (e.g: server.com:5155)
     * @param  int    $owner   The ID of the server owner
     * @return Server An object that represents the sent message
     */
    public static function addServer($name, $address, $owner)
    {
        $server = self::create(array(
            'name' => $name,
            'address' => $address,
            'owner' => $owner,
            'status' => 'active',
        ), 'ssis', 'updated');
        $server->forceUpdate();

        return $server;
    }

    /**
     * Update the server with current bzfquery information
     */
    public function forceUpdate()
    {
        $this->info = @bzfquery($this->address);
        $this->updated = TimeDate::now();
        $this->db->query("UPDATE servers SET info = ?, updated = NOW() WHERE id = ?", "si", array(serialize($this->info), $this->id));
    }

    /**
     * Checks if the server is online (listed on the public list server)
     * @return bool Whether the server is online
     */
    public function isOnline()
    {
        $servers = file(LIST_SERVER);
        foreach ($servers as $server) {
            list($host, $protocol, $hex, $ip, $title) = explode(' ', $server, 5);
            if ($this->address == $host) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the server has players
     * @return bool Whether the server has any players
     */
    public function hasPlayers()
    {
        return $this->info['numPlayers'] > 0;
    }

    /**
     * Gets the number of players on the server
     * @return int The number of players
     */
    public function numPlayers()
    {
        return $this->info['numPlayers'];
    }

    /**
     * Gets the players on the server
     * @return array The players on the server
     */
    public function getPlayers()
    {
        if (isset($this->info['player']))
            return $this->info['player'];

        return array();
    }

    /**
     * Checks if the last update is older than or equal to the update interval
     * @return bool Whether the information is older than the update interval
     */
    public function staleInfo()
    {
        $update_time = $this->updated->copy();
        $update_time->addMinutes(UPDATE_INTERVAL);

        return TimeDate::now()->gte($update_time);
    }

    /**
     * Gets the server's ip address
     * @return string The server's ip address
     */
    public function getServerIp()
    {
        return $this->info['ip'];
    }

    /**
     * Get the server's name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the server's IP address or hostname
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get when the server information was last updated
     * @return TimeDate
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Returns the amount of time passed since the server was
     * last updated in a human-readable form
     * @return string
     */
    public function lastUpdate()
    {
        return $this->updated->diffForHumans();
    }

    /**
     * Get all the servers in the database that have an active status
     * @return Server[] An array of server objects
     */
    public static function getServers()
    {
        return self::arrayIdToModel(self::fetchIdsFrom("status", array("active"), "s", false, "ORDER BY name"));
    }

}
