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
     * The domain of the server
     * @var string
     */
    protected $domain;

    /**
     * The port of the server
     * @var int
     */
    protected $port;

    /**
     * The id of the country the server is located in
     * @var int
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
     * The ID of the API key assigned to this server
     * @var ApiKey
     */
    protected $api_key;

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
     * {@inheritdoc}
     */
    protected function assignResult($server)
    {
        $this->name = $server['name'];
        $this->domain = $server['domain'];
        $this->port = $server['port'];
        $this->country = $server['country'];
        $this->owner = $server['owner'];
        $this->online = $server['online'];
        $this->info = unserialize($server['info']);
        $this->api_key = ApiKey::get($server['api_key']);
        $this->updated = TimeDate::fromMysql($server['updated']);
        $this->status = $server['status'];
    }

    /**
     * Add a new server
     *
     * @param string $name    The name of the server
     * @param string $domain  The domain of the server (e.g. server.com)
     * @param string $port    The port of the server (e.g. 5154)
     * @param int    $country The ID of the country
     * @param int    $owner   The ID of the server owner
     *
     * @return Server An object that represents the sent message
     */
    public static function addServer($name, $domain, $port, $country, $owner)
    {
        $key = ApiKey::getKeyByOwner($owner);

        $server = self::create(array(
            'name'    => $name,
            'domain'  => $domain,
            'port'    => $port,
            'country' => $country,
            'owner'   => $owner,
            'api_key' => $key->getId(),
            'status'  => 'active',
        ), 'ssiiiis', 'updated');
        $server->forceUpdate();

        return $server;
    }

    /**
     * Update the server with current bzfquery information
     * return self
     */
    public function forceUpdate()
    {
        $this->info = bzfquery($this->getAddress());
        $this->updated = TimeDate::now();
        $this->online = !isset($this->info['error']);

        $this->db->query(
        "UPDATE servers SET info = ?, online = ?, updated = UTC_TIMESTAMP() WHERE id = ?",
        "sii",
        array(serialize($this->info), $this->online, $this->id)
    );

    // If a server is offline, log it
        if (!$this->online) {
            if ($logger = \Service::getContainer()->get('logger')) {
                $id = $this->getId();
                $address = $this->getAddress();
                $reason = $this->info['error'];

                $logger->notice("Connection to server #$id ($address) failed: $reason");
            }
        }

        return $this;
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
     * The ApiKey assigned to this server
     *
     * @return \CachedModel|int|null|static
     */
    public function getApiKey()
    {
        return $this->api_key;
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
     * Get the domain of the server
     *
     * @return string The server's domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the port of the server
     *
     * @return int The port number
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the server's IP address or hostname
     * @return string
     */
    public function getAddress()
    {
        return $this->domain . ":" . $this->port;
    }

    /**
     * Get when the server information was last updated
     * @return TimeDate
     */
    public function getUpdated()
    {
        return $this->updated->copy();
    }

    /**
     * Get the country the server is in
     * @return Country The country the server is located in
     */
    public function getCountry()
    {
        return Country::get($this->country);
    }

    /**
     * Get the owner of the server
     * @return Player
     */
    public function getOwner()
    {
        return Player::get($this->owner);
    }

    /**
     * Returns the amount of time passed since the server was last updated
     * @return TimeDate
     */
    public function getLastUpdate()
    {
        return $this->updated;
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
     * @deprecated Use setDomain() and setPort() instead
     *
     * @return self
     */
    public function setAddress($address)
    {
        list($domain, $port) = explode(":", $address);

        $this->setDomain($domain);
        $this->setPort($port);

        return $this;
    }

    /**
     * Set the domain of the server
     *
     * @param $domain string The new domain of the server
     *
     * @return self
     */
    public function setDomain($domain)
    {
        return $this->updateProperty($this->domain, 'domain', $domain, 's');
    }

    /**
     * Set the port of the server
     *
     * @param $port int The new port of the server
     *
     * @return self
     */
    public function setPort($port)
    {
        return $this->updateProperty($this->port, 'port', $port, 'i');
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
     * Set the id of the country of the server
     *
     * @param int $countryId The ID of the new country of the server
     *
     * @return self
     */
    public function setCountry($countryId)
    {
        return $this->updateProperty($this->country, 'country', $countryId, 'i');
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
