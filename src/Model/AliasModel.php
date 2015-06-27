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
abstract class AliasModel extends UrlModel implements NamedModel
{
    /**
     * The name of the object
     * @var string
     */
    protected $name;

    /**
     * A unique URL-friendly identifier for the object
     * @var string
     */
    protected $alias;

    /**
     * Get the name of the object
     * @var string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the name of the team, safe for use in your HTML
     *
     * @return string The name of the team
     */
    public function getEscapedName()
    {
        if (!$this->valid) {
            return "<em>None</em>";
        }

        return $this->escape($this->name);
    }

    /**
     * Change the object's name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->updateProperty($this->name, 'name', $name, 's');
        $this->resetAlias();

        return $this;
    }

    /**
     * Get an object's alias
     * @return string|int The alias (or ID if the alias doesn't exist)
     */
    public function getAlias()
    {
        if ($this->alias !== null && $this->alias != "") {
            return $this->alias;
        }

        return $this->getId();
    }

    /**
     * Set a model's alias
     * @param  string $alias The new alias
     * @return void
     */
    protected function setAlias($alias)
    {
        $this->updateProperty($this->alias, 'alias', $alias, 's');
    }

    /**
     * Reset a model's alias based on its name
     * @return self
     */
    public function resetAlias()
    {
        $alias = static::generateAlias($this->name, $this->id);

        return $this->updateProperty($this->alias, 'alias', $alias, 's');
    }

    /**
     * {@inheritDoc}
     */
    public function getURL($action = 'show', $absolute = false, $params = array())
    {
        if (!$this->isValid()) {
            return "";
        }

        return $this->getLink($this->getAlias(), $action, $absolute, $params);
    }

    /**
     * Gets an entity from the supplied alias
     * @param  string     $alias The object's alias
     * @return AliasModel
     */
    public static function fetchFromAlias($alias)
    {
        return static::get(self::fetchIdFrom($alias, "alias"));
    }

    /**
     * {@inheritDoc}
     * @return AliasModel
     */
    public static function fetchFromSlug($slug)
    {
        if (ctype_digit((string) $slug)) {
            // Slug is an integer, we can fetch by ID
            return static::get((int) $slug);
        } else {
            // Slug is something else, we can fetch by alias
            return self::fetchFromAlias($slug);
        }
    }

    /**
     * Generate a URL-friendly unique alias for an object name
     *
     * @param  string      $name The original object name
     * @param  int|Null    $id   The ID of the object, if it's being edited and not created
     * @return string|Null The generated alias, or Null if we couldn't make one
     */
    public static function generateAlias($name, $id = null)
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
            $name = $name.'-';
        }

        return self::getUniqueAlias($name, ($id) ?: 0);
    }

    /**
     * Make sure that the generated alias provided is unique
     *
     * @param  string $alias The alias
     * @param  int    $id    The ID of the object, if it's being edited and not created
     * @return string An alias that is guaranteed to be unique
     */
    private static function getUniqueAlias($alias, $id = 0)
    {
        // Try to find duplicates
        $db = Database::getInstance();
        $result = $db->query("SELECT alias FROM " . static::TABLE . " WHERE id != ? AND alias REGEXP ?", 'is', array($id, "^" . $alias . "[0-9]*$"));

        // Convert the multi-dimensional array that $db->query() gave us into
        // a single-dimensional one.
        $aliases = (is_array($result)) ? array_column($result, 'alias') : array();

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
     * Disallowed aliases will have a number appended, so the URL would be
     * http://example.com/bzion/teams/new2
     *
     * @return string[]
     */
    protected static function getDisallowedAliases()
    {
        return array('new');
    }
}
