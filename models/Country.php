<?php
/**
 * This file contains functionality relating to the countries players are allowed to set in their profiles as their location
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

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
    public function __construct($id) {

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
    public function getName() {
        return $this->name;
    }

    /**
     * Get the country's flag
     * @return string The URL to the country's flag
     */
    public function getFlag() {
        return $this->flag;
    }

    /**
     * Get all the countries in the database
     * @return Country[] An array of country objects
     */
    public static function getCountries() {
        return self::arrayIdToModel(self::fetchIds());
    }

}
