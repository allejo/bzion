<?php
/**
 * This file contains functionality relating to database objects that have a custom URL such as teams and players
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A Model that has a URL and an alias
 * @package    BZiON\Models
 */
abstract class AliasModel extends UrlModel
{
    /**
     * A unique URL-friendly identifier for the object
     * @var string
     */
    protected $alias;

    /**
     * Get an object's alias
     * @return string|int The alias (or ID if the alias doesn't exist)
     */
    public function getAlias()
    {
        if ($this->alias !== null && $this->alias != "")
            return $this->alias;
        return $this->getId();
    }

    /**
     * Set a model's alias
     * @param  string $alias The new alias
     * @return void
     */
    protected function setAlias($alias)
    {
        $this->alias = $alias;
        $this->update('alias', $alias, 's');
    }

    /**
     * {@inheritDoc}
     */
    public function getURL($action='show', $absolute=false)
    {
        if (!$this->isValid())
            return "";
        return Service::getGenerator()->generate(static::getRouteName($action),
                array(static::getParamName() => $this->getAlias()), $absolute);
    }

    /**
     * Gets an entity from the supplied alias
     * @param  string     $alias The object's alias
     * @return AliasModel
     */
    public static function fetchFromAlias($alias)
    {
        return new static(self::fetchIdFrom($alias, "alias"));
    }

    /**
     * {@inheritDoc}
     * @return AliasModel
     */
    public static function fetchFromSlug($slug)
    {
        if (ctype_digit((string) $slug)) {
            // Slug is an integer, we can fetch by ID
            return new static((int) $slug);
        } else {
            // Slug is something else, we can fetch by alias
            return self::fetchFromAlias($slug);
        }
    }

    /**
     * Generate a URL-friendly unique alias for an object name
     *
     * @param  string      $name The original object name
     * @return string|Null The generated alias, or Null if we couldn't make one
     */
    public static function generateAlias($name)
    {
        // Convert name to lowercase
        $name = strtolower($name);

        // List of characters which should be converted to dashes
        $makeDash = array(' ', '_');

        $name = str_replace($makeDash, '-', $name);

        // Only keep letters, numbers and dashes - delete everything else
        $name = preg_replace("/[^a-zA-Z\-0-9]+/", "", $name);

        if (str_replace('-', '', $name) == '') {
            // The name only contains symbols or Unicode characters!
            // This means we can't convert it to an alias
            return null;
        }

        // An alias name can't only contain numbers, because it will be
        // indistinguishable from an ID. If it does, add a dash in the end.
        // Also prevent aliases from taking names such as "new",
        while (preg_match("/^[0-9]+$/", $name)) {
            $name = $name . '-';
        }

        return self::getUniqueAlias($name);
    }

    /**
     * Make sure that the generated alias provided is unique
     *
     * @param  string $alias The alias
     * @return string An alias that is guaranteed to be unique
     */
    private static function getUniqueAlias($alias)
    {
        // Try to find duplicates
        $db = Database::getInstance();
        $result = $db->query("SELECT alias FROM " . static::TABLE . " WHERE alias REGEXP ?", 's', array("^" . $alias . "[0-9]*$"));

        // Convert the multi-dimensional array that $db->query() gave us into
        // a single-dimensional one.
        $aliases = array();
        if (is_array($result)) {
            foreach ($result as $r) {
                $aliases[] = $r['alias'];
            }
        }

        // If there's already an entry with the alias we generated, put a number
        // in the end of it and keep incrementing it until there is we find
        // an open spot.
        $currentAlias = $alias;
        for ($i = 2;; $i++) {
            if (!in_array($currentAlias, $aliases)
            &&  !in_array($currentAlias, static::getDisallowedAliases())) {
                break;
            }

            $currentAlias = $alias . $i;
        }

        return $currentAlias;
    }

    /**
     * Get a list of aliases that should not be given to objects
     *
     * For example, you want to prevent teams from getting the "new" alias.
     * Otherwise, the team's link would be http://example.com/bzion/teams/new,
     * and the user would go to the team creation page instead of the team's page.
     * Disallowed aliases will have a dash appended, so the URL would be
     * http://example.com/bzion/teams/new-
     *
     * @return string[]
     */
    protected static function getDisallowedAliases()
    {
        return array('new');
    }
}
