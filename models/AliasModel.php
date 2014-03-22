<?php

/**
 * A Model that has a URL and an alias
 */
abstract class AliasModel extends UrlModel {
    /**
     * A unique URL-friendly identifier for the object
     * @var string
     */
    protected $alias;

    /**
     * Get an object's alias
     * @return string|int The alias (or ID if the alias doesn't exist)
     */
    public function getAlias() {
        if ($this->alias !== null && $this->alias != "")
            return $this->alias;
        return $this->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getURL($absolute=false) {
        if (!$this->isValid())
            return "";
        return Service::getGenerator()->generate(static::getRouteName(), array(static::getParamName() => $this->getAlias()), $absolute);
    }

    /**
     * Gets an entity from the supplied alias
     * @param string $alias The object's alias
     * @return AliasModel
     */
    public static function fetchFromAlias($alias) {
        return new static(self::fetchIdFrom($alias, "alias"));
    }

    /**
     * {@inheritDoc}
     * @return AliasModel
     */
    public static function fetchFromSlug($slug) {
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
     * @param string $name The original object name
     * @return string|Null The generated alias, or Null if we couldn't make one
     */
    public static function generateAlias($name) {
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
        if (preg_match("/^[0-9]+$/", $name)) {
            $name = $name . '-';
        }

        // Try to find duplicates
        $db = Database::getInstance();
        $result = $db->query("SELECT alias FROM " . static::TABLE . " WHERE alias REGEXP ?", 's', array("^" . $name . "[0-9]*$"));

        // The functionality of the following code block is provided in PHP 5.5's
        // array_column function. What is does is convert the multi-dimensional
        // array that $db->query() gave us into a single-dimensional one.
        $aliases = array();
        if (is_array($result)) {
            foreach ($result as $r) {
                $aliases[] = $r['alias'];
            }
        }

        // No duplicates found
        if (!in_array($name, $aliases))
            return $name;

        // If there's already an entry with the alias we generated, put a number
        // in the end of it and keep incrementing it until there is we find
        // an open spot.
        $i = 2;
        while (in_array($name . $i, $aliases)) {
            $i++;
        }

        return $name . $i;
    }
}
