<?php
/**
 * This file allows models to be cached to prevent unnecessary queries to the database
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A database object with the ability to be cached in the memory
 * @package    BZiON\Models
 */
abstract class CachedModel extends BaseModel
{
    /**
     * {@inheritDoc}
     */
    public function __construct($id)
    {
        $this->id = (int) $id;

        if (!$this->retrieveFromCache()) {
            $this->getFromDatabase();
        }
    }

    /**
     * Find out whether the current model exists in the model cache
     * @return bool
     */
    protected function existsInCache()
    {
        if (!Service::getModelCache()) {
            return false;
        }

        return Service::getModelCache()->has(get_class($this), $this->id);
    }

    /**
     * Store the current model in the cache so it can be retrieved later
     * @return void
     */
    protected function storeInCache()
    {
        if (!Service::getModelCache()) {
            return;
        }

        Service::getModelCache()->save($this);
    }

    /**
     * Load the current object's properties from the cache
     * @return bool True if the assignement was successful, false if the model
     *              doesn't exist in the cache
     */
    protected function retrieveFromCache()
    {
        if (!$this->existsInCache()) {
            return false;
        }

        $cachedModel = Service::getModelCache()->get(get_class($this), $this->id);
        $this->copy($cachedModel);

        return true;
    }

    /**
     * Fetch a model's data from the database again
     * @return static The new model
     */
    public function refresh()
    {
        $this->getFromDatabase();

        return $this;
    }

    /**
     * Get a model's data from the database
     */
    private function getFromDatabase()
    {
        parent::__construct($this->id);

        if ($this->loaded) {
            // Load the lazy parameters of the model if they're loaded already
            $this->lazyLoad(true);
        }

        $this->storeInCache();
    }

    /**
     * Clone a model into $this
     * @return void
     */
    private function copy($model)
    {
        foreach ($model as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * {@inheritDoc}
     * @param string $types
     */
    protected static function create($params, $types, $now = null, $table = '')
    {
        $model = parent::create($params, $types, $now, $table);
        $model->storeInCache();

        return $model;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     */
    public function update($name, $value, $type = 'i')
    {
        parent::update($name, $value, $type);
        $this->storeInCache();
    }
}
