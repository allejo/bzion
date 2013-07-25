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

}
