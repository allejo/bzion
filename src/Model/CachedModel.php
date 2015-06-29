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
    public static function get($id)
    {
        if (is_object($id)) {
            return parent::get($id);
        }

        $cache = Service::getModelCache();
        $id = (int) $id;

        if (!$cache) {
            // There is no cache, just get the model
            return parent::get($id);
        }

        if ($model = self::getFromCache($id)) {
            // The model exists in the cache, return that to the caller
            return $model;
        } else {
            return parent::get($id)->storeInCache();
        }
    }

    /**
     * Find out whether a model exists in the cache
     *
     * @param  int  $id The ID of the model
     * @return bool     True if the model exists in the cache
     */
    private static function existsInCache($id)
    {
        $cache = Service::getModelCache();

        if (!$cache) {
            return false;
        }

        return $cache->has(get_called_class(), $id);
    }

    /**
     * Get a model from the cache
     *
     * @param  int         $id The ID of the model
     * @return static|null     The model if it's found, null if it doesn't exist
     */
    private static function getFromCache($id)
    {
        if (!self::existsInCache($id)) {
            return null;
        }

        return Service::getModelCache()->get(get_called_class(), $id);
    }

    /**
     * Store the model in the cache
     *
     * @return self
     */
    protected function storeInCache()
    {
        if (!Service::getModelCache()) {
            return;
        }

        Service::getModelCache()->save($this);

        return $this;
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
        parent::get($this->id);

        if ($this->loaded) {
            // Reload the lazy parameters of the model if they're loaded already
            $this->lazyLoad(true);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected static function create($params, $types, $now = null, $table = '')
    {
        $model = parent::create($params, $types, $now, $table);
        $model->storeInCache();

        return $model;
    }
}
