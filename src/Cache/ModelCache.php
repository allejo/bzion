<?php

/**
 * A Model cache that can speed up data retrieval from the database
 */
class ModelCache
{
    /**
     * The models saved in the cache
     * @var array[]
     */
    private $models = array();

    /**
     * Save a model in the database cache
     * @param  Model $model the model to save
     * @return Model The stored model
     */
    public function save($model)
    {
        $type = get_class($model);

        if (!isset($this->models[$type]))
            $this->models[$type] = array();

        $this->models[$type][$model->getId()] = $model;

        return $model;
    }

    /**
     * Get a model from the database cache
     * @param  string $type    The type of the model (Player, Team etc.)
     * @param  int    $id      The database ID of the model
     * @param  mixed  $default What to return if the model doesn't exist in the cache
     * @return mixed  The Model if it exists in the cache, or $default if it
     *                        wasn't found
     */
    public function get($type, $id, $default=null)
    {
        if (!$this->has($type, $id))
            return $default;

        return $this->models[$type][$id];
    }

    /**
     * Find whether a model exists in the cache
     * @param  string $type The type of the model (Player, Team etc.)
     * @param  int    $id   The database ID of the model
     * @return bool   True if it exists, false if not
     */
    public function has($type, $id)
    {
        return isset($this->models[$type][$id]);
    }

    /**
     * Remove all the entries from the cache
     * @return void
     */
    public function clear()
    {
        $this->models = array();
    }

}
