<?php
/**
 * This file contains functionality relating to the countries players are allowed to set in their profiles as their location
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A country
 */
class Country extends Model implements NamedModel
{
    /** @var string The name of the country */
    protected $name;

    /** @var string The ISO code of the country */
    protected $iso;

    const DELETED_COLUMN = 'is_deleted';
    const TABLE = 'countries';

    /**
     * {@inheritdoc}
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
        return '<div class="c-flag ' . $this->getFlagCssClass() . '" aria-hidden="true" title="' . $this->getName() . '"></div>';
    }

    /**
     * Get the country's flag's CSS class
     *
     * @return string The URL to the country's flag
     */
    private function getFlagCssClass()
    {
        return "c-flag--" . strtolower($this->getISO());
    }

    /**
     * Get a query builder for countries
     *
     * @throws Exception When no database is configured for BZiON
     *
     * @return QueryBuilderFlex
     */
    public static function getQueryBuilder()
    {
        return QueryBuilderFlex::createForModel(Country::class)
            ->setNameColumn('name')
        ;
    }
}
