<?php
/**
 * This file contains functionality relating to the official BZFlag match servers for the league
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

include_once DOC_ROOT . "/includes/bzfquery.php";

/**
 * A BZFlag server
 * @package    BZiON\Models
 */
class Server extends UrlModel implements NamedModel
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
     * The id of the country the server is located in
     * @var Country
     */
    protected $country;

    /**
     * The id of the owner of the server
     * @var int
     */
    protected $owner;

    /**
     * Whether the server is listed on the public list server
     * @var bool
     */
    protected $online;

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

    const CREATE_PERMISSION = Permission::ADD_SERVER;
    const EDIT_PERMISSION = Permission::EDIT_SERVER;
    const SOFT_DELETE_PERMISSION = Permission::SOFT_DELETE_SERVER;
    const HARD_DELETE_PERMISSION = Permission::HARD_DELETE_SERVER;

    /**
     * {@inheritDoc}
     */
    protected function assignResult($server)
    {
        $this->name = $server['name'];
        $this->address = $server['address'];
        $this->country = new Country($server['country']);
        $this->owner = $server['owner'];
        $this->online = $server['online'];
        $this->info = unserialize($server['info']);
        $this->updated = TimeDate::fromMysql($server['updated']);
        $this->status = $server['status'];
    }

    /**
     * Add a new server
     *
     * @param string $name    The name of the server
     * @param string $address The address of the server (e.g: server.com:5155)
     * @param int    $country The ID of the country
     * @param int    $owner   The ID of the server owner
     *
     * @return Server An object that represents the sent message
     */
    public static function addServer($name, $address, $country, $owner)
    {
        $server = self::create(array(
            'name'    => $name,
            'address' => $address,
            'country' => $country,
            'owner'   => $owner,
            'status'  => 'active',
        ), 'ssiis', 'updated');
        $server->forceUpdate();

        return $server;
    }

    /**
     * Update the server with current bzfquery information
     * return self
     */
    public function forceUpdate()
    {
        $this->info = @bzfquery($this->address);
        $this->updated = TimeDate::now();
        $this->db->query("UPDATE servers SET info = ?, updated = UTC_TIMESTAMP() WHERE id = ?", "si", array(serialize($this->info), $this->id));

        $this->updateOnline();

        return $this;
    }

    /**
     * Checks if the server is online (listed on the public list server)
     * @todo   Fix performance issues (many calls to the list server)
     * @return self
     */
    private function updateOnline()
    {
        $online = false;
        $listServer = Service::getParameter('bzion.miscellaneous.list_server');
        $servers = file($listServer);

        foreach ($servers as $server) {
            list($host, $protocol, $hex, $ip, $title) = explode(' ', $server, 5);
            if ($this->address == $host) {
                $online = true;
            }
        }

        return $this->updateProperty($this->online, 'online', $online, 'i');
    }

    /**
     * Checks if the server is online (listed on the public list server)
     * @return bool Whether the server is online
     */
    public function isOnline()
    {
        return $this->online;
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
        return (isset($this->info['numPlayers'])) ? $this->info['numPlayers'] : 0;
    }

    /**
     * Gets the players on the server
     * @return array The players on the server
     */
    public function getPlayers()
    {
        if (isset($this->info['player'])) {
            return $this->info['player'];
        }

        return array();
    }

    /**
     * Checks if the last update is older than or equal to the update interval
     * @return bool Whether the information is older than the update interval
     */
    public function staleInfo()
    {
        $update_time = $this->updated->copy();
        $update_time->modify(Service::getParameter('bzion.miscellaneous.update_interval'));

        return TimeDate::now() >= $update_time;
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
     * Get the country the server is in
     * @return Country The country the server is located in
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Get the owner of the server
     * @return Player
     */
    public function getOwner()
    {
        return new Player($this->owner);
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
     * Set the name of the server
     *
     * @param string $name The new name of the server
     *
     * @return self
     */
    public function setName($name)
    {
        return $this->updateProperty($this->name, 'name', $name, 's');
    }

    /**
     * Set the address of the server
     *
     * @param string $address The new address of the server
     *
     * @return self
     */
    public function setAddress($address)
    {
        return $this->updateProperty($this->address, 'address', $address, 's');
    }

    /**
     * Set the id of the owner of the server
     *
     * @param int $ownerId The ID of the new owner of the server
     *
     * @return self
     */
    public function setOwner($ownerId)
    {
        return $this->updateProperty($this->owner, 'owner', $ownerId, 'i');
    }

    /**
     * Get all the servers in the database that have an active status
     * @return Server[] An array of server objects
     */
    public static function getServers()
    {
        return self::arrayIdToModel(self::fetchIdsFrom("status", array("active"), "s", false, "ORDER BY name"));
    }

    /**
     * Get a query builder for servers
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new QueryBuilder('Server', array(
            'columns' => array(
                'name'   => 'name',
                'status' => 'status'
            ),
            'name' => 'name'
        ));
    }
}
