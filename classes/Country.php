<?php

class Country extends Controller {

    /**
     * The name of the country
     * @var string
     */
    private $name;

    /**
     * The flag of the country
     * @var string
     */
    private $flag;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "countries";

    /**
     * Construct a new Country
     * @param int $id The country's id
     */
    function __construct($id) {

        parent::__construct($id);
        if (!$this->valid) return;

        $country = $this->result;

        $this->name = $country['name'];
        $this->flag = $country['flag'];

    }

    function getName() {
        return $this->name;
    }

    function getFlag() {
        return $this->flag;
    }

    /**
     * Get all the countries in the database
     * @param string $select The column to retrieve from the database
     * @return array An array of country IDs
     */
    public static function getCountries($select = "id") {
        return parent::getIds($select);
    }

}
