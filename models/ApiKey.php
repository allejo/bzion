<?php

class ApiKey extends Model
{
    protected $name;

    protected $owner;

    protected $key;

    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "api_keys";

    /**
     * {@inheritdoc}
     */
    protected function assignResult($key)
    {
        $this->name = $key['name'];
        $this->owner = $key['owner'];
        $this->key = $key['key'];
    }

    public static function createKey($name, $owner)
    {
        $key = self::create(array(
            'name'  => $name,
            'owner' => $owner,
            'key'   => self::generateKey()
        ), 'sis');

        return $key;
    }

    public static function getKeyByOwner($owner)
    {
        $key = parent::fetchIdFrom($owner, "owner", 'i');

        if ($key == null)
        {
            $key = self::create("Automatically generated key", $owner);
        }

        return $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOwner()
    {
        return Player::get($this->owner);
    }

    public function getStatus()
    {
        return $this->status;
    }

    public static function getKeys($owner = -1)
    {
        if ($owner > 0)
        {
            $ids = self::fetchIdsFrom("owner", array($owner), "i", false, "WHERE status = 'active'");
            return self::arrayIdToModel($ids);
        }

        $ids = self::fetchIdsFrom("status", array("active"), "s");
        return self::arrayIdToModel($ids);
    }

    protected static function generateKey()
    {
        list($usec, $sec) = explode(' ', microtime());
        $seed = (float) $sec + ((float) $usec * 100000);
        srand($seed);

        $key = rand();

        return substr(sha1($key), 24);
    }
}