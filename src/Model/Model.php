<?php
/**
 * This file contains the skeleton for all of the database objects
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A database object (e.g. A player or a team)
 * @package    BZiON\Models
 */
abstract class Model extends CachedModel
{
    /**
     * Generates a string with the object's type and ID
     */
    public function __toString()
    {
        return get_class($this) . " #" . $this->getId();
    }

    /**
     * Find if the model is in the trash can (or doesn't exist)
     *
     * @return bool True if the model has been deleted
     */
    public function isDeleted()
    {
        if (!$this->isValid() || $this->getStatus() == 'deleted') {
            return true;
        }

        return false;
    }

    /**
     * Find if the model is active (i.e. visible to everyone)
     *
     * @return bool
     */
    public function isActive()
    {
        return in_array($this->getStatus(), $this->getActiveStatuses());
    }

    /**
     * Get the models's status
     *
     * @return string
     */
    public function getStatus()
    {
        if (!isset($this->status)) {
            return 'active';
        }

        return $this->status;
    }

    /**
     * Find if two objects represent the same model
     *
     * @param  Model   $model The model to compare
     * @return bool
     */
    public function isSameAs(Model $model)
    {
        if (!$this->valid || !$model->valid) {
            return false;
        }

        $sameType = $this instanceof $model || $model instanceof $this;

        return $sameType && $this->id === $model->id;
    }

    /**
     * Get the possible statuses representing an active model (visible to everyone)
     *
     * @return string[]
     */
    public static function getActiveStatuses()
    {
        return array('active');
    }

    /**
     * Converts an array of IDs to an array of Models
     * @param  int[] $idArray The list of IDs
     * @return array An array of models
     */
    public static function arrayIdToModel($idArray)
    {
        $return = array();
        foreach ($idArray as $id) {
            $return[] = static::get($id);
        }

        return $return;
    }

    /**
     * Converts an array of Models to an array of IDs
     *
     * All model type information is lost
     *
     * @param  ModelInterface[] $modelArray The list of models
     * @return int[] An array of IDs
     */
    public static function mapToIDs($modelArray)
    {
        return array_map(function (ModelInterface $model) {
            return $model->getId();
        }, $modelArray);
    }

    /**
     * Update a property and the corresponding database column
     *
     * @param  mixed  $property The protected class property to update
     * @param  string $dbColumn The name of the database column to update
     * @param  mixed  $value    The value to insert
     * @param  string $type     The mysqli type of the value (s, i, d, b)
     * @return self   Returns the model itself to allow method chaining
     */
    protected function updateProperty(&$property, $dbColumn, $value, $type = 'i')
    {
        // Don't waste time with mysql if there aren't any changes
        if ($property !== $value) {
            $property = $value;

            if ($value instanceof TimeDate) {
                $value = $value->toMysql();
            }

            $this->update($dbColumn, $value);
        }

        return $this;
    }

    /**
     * Gets the type of the model
     * @return string The type of the model, e.g. "server"
     */
    public static function getType()
    {
        return self::toSnakeCase(get_called_class());
    }

    /**
     * Gets a human-readable format of the model's type
     * @return string
     */
    public static function getTypeForHumans()
    {
        return self::getType();
    }

    /**
     * Change a parameter if a model is not valid
     *
     * Useful for form validation
     *
     * @param  string $property The name of the property to change
     * @param  mixed  $value    The value of the property
     * @return self
     */
    protected function inject($property, $value)
    {
        if (!$this->isValid()) {
            $this->{$property} = $value;
        }

        return $this;
    }

    /**
     * Takes a CamelCase string and converts it to a snake_case one
     * @param  string $input The string to convert
     * @return string
     */
    private static function toSnakeCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = ($match == strtoupper($match)) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * Escape special HTML characters from a string
     * @param  string  $string
     * @return $string
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Create model objects, given their MySQL entries
     *
     * @param  array $results The MySQL rows of the model
     * @return static[]
     */
    public static function createFromDatabaseResults(&$results)
    {
        $models = array();

        foreach ($results as $result) {
            $model = new static($result['id'], $result);
            $model->storeInCache();

            $models[] = $model;
        }

        return $models;
    }
}
