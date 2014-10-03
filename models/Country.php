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
    protected $name;

    /**
     * The ISO code of the country
     * @var string
     */
    protected $iso;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "countries";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($country)
    {
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
     * Get an associative array with country ISO code as keys and country names
     * as values
     *
     * @return array
     */
    public static function getCountriesWithISO()
    {
        $result = Database::getInstance()->query(
            'SELECT iso, name from ' . static::TABLE
        );

        return array_column($result, 'name', 'iso');
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

    /**
     * Given a country's ISO, get its ID
     *
     * @param  string $iso The two-letter ISO code of the country
     * @return int    The country's database ID
     */
    public static function getIdFromISO($iso)
    {
        return self::fetchIdFrom($iso, 'iso');
    }
}
