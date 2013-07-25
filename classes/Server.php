<?php

include_once("bzfquery.php");

class Server {

    private $id;
    private $name;
    private $address;
    private $owner;
    private $info;
    private $updated;

    private $db;

    function __construct($id) {

        $this->db = new Database();
        $this->id = $id;

        $results = $this->db->query("SELECT * FROM servers WHERE id = ?", "i", array($id));

        $this->name = $results['name'];
        $this->address = $results['address'];
        $this->owner = $results['owner'];
        $this->info = unserialize($results['info']);
        $this->updated = new DateTime($results['updated']);

    }


    function __set($name, $value) {
        switch ($name) {
            case 'name':
                $this->db->query("UPDATE servers SET name = ? WHERE id = ?", "si", array($value, $this->id));
                $this->name = $value;
                break;
            case 'address':
                $this->db->query("UPDATE servers SET address = ? WHERE id = ?", "si", array($value, $this->id));
                $this->address = $value;
                break;
            case 'owner':
                $this->db->query("UPDATE servers SET owner = ? WHERE id = ?", "si", array($value, $this->id));
                $this->owner = $value;
                break;
            case 'info':
                $this->db->query("UPDATE servers SET info = ? WHERE id = ?", "si", array(serialize($value), $this->id));
                $this->info = $value;
                break;
            case 'updated':
                $this->db->query("UPDATE servers SET updated = ? WHERE id = ?", "si", array($value, $this->id));
                $this->updated = $value;
                break;
        }
    }

    function force_update() {
    	$this->info = bzfquery($this->address);
    	$this->db->query("UPDATE servers SET info = ? WHERE id = ?", "si", array(serialize($this->info), $this->id));
    }

    function is_online() {
        $servers = file(LIST_SERVER);
        return in_array($this->address, $servers);
    }

    function has_players() {
        return $this->info['numPlayers'] > 0;
    }

    function num_players() {
        return $this->info['numPlayers'];
    }

    function get_players() {
        return $this->info['player'];
    }

    /*
     * Returns if the last update was done greater than UPDATE_INTERVAL mins ago
     */
    function stale_info() {
        $now = new DateTime("now");
        $last_update = $this->updated->diff($now);

        return $last_update->format('%i') > UPDATE_INTERVAL;
    }

    function server_ip() {
        return $this->info['ip'];
    }

}
