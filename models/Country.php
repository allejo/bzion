<?php
/**
 * This file contains functionality relating to the countries players are allowed to set in their profiles as their location
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A country
 * @package    BZiON\Models
 */
class Country extends Model
{
    /**
     * The name of the country
     * @var string
     */
    private $name;

    /**
     * The ISO code of the country
     * @var string
     */
    private $iso;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "countries";

    /**
     * Construct a new Country
     *
     * @param int $id The country's id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        if (!$this->valid) return;

        $country = $this->result;

        $this->name = $country['name'];
        $this->iso = $country['iso'];
    }

    /**
     * Get the name of the country in the default language
     *
     * @return string The name of the country
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the ISO code of a country
     *
     * @return string The ISO code of the country
     */
    public function getISO()
    {
        return $this->iso;
    }

    /**
     * Get the HTML to display a specific flag
     *
     * @return string HTML to generate a flag
     */
    public function getFlagLiteral()
    {
        return '<div class="flag ' . $this->getFlagCssClass() . '" title="' . $this->getName() . '"></div>';
    }

    /**
     * Get all the countries in the database
     *
     * @return Country[] An array of country objects
     */
    public static function getCountries()
    {
        return self::arrayIdToModel(self::fetchIds());
    }

    /**
     * Get the country's flag's CSS class
     *
     * @return string The URL to the country's flag
     */
    private function getFlagCssClass()
    {
        return "flag-" . strtolower($this->getISO());
    }
}
