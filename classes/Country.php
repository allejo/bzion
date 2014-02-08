<?php

/**
 * A country
 */
class Country extends Model {

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

    /**
     * Get the name of the country in the default language
     * @return string
     */
    function getName() {
        return $this->name;
    }

    /**
     * Get the country's flag
     * @return string The URL to the country's flag
     */
    function getFlag() {
        return $this->flag;
    }

    /**
     * Get all the countries in the database
     * @return array An array of country IDs
     */
    public static function getCountries() {
        return parent::getIds();
    }

}
