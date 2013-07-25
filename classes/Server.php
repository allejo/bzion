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

    function force_update() {
    	$this->info = bzfquery($this->address);
    	$this->db->query("UPDATE servers SET info = ? WHERE id = ?", "si", array(serialize($info), $this->id));
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

}
