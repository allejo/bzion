<?php

use Symfony\Bundle\FrameworkBundle\Client;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /** @var \Faker\Generator */
    protected $faker;
    protected $createdModels = [];

    /**
     * The BZID of the last player created, used to prevent conflicts when creating new players
     * @var int
     */
    private $lastBzid = 200;

    /**
     * A list of all the players created, used to wipe them on the tearDown() method
     * @var array
     */
    private $playersCreated = array();

    /**
     * Makes sure that a connection to the MySQL database has been achieved
     * @return Database
     */
    public static function connectToDatabase()
    {
        return Database::getInstance();
    }

    /**
     * Creates a Client.
     *
     * @param array $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    public static function createClient(array $server = array())
    {
        $client = Service::getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    /**
     * Asserts that two arrays have the same values
     *
     * @param array|ArrayAccess $expectedArray
     * @param array|ArrayAccess $array
     * @param string            $message
     */
    public static function assertArraysHaveEqualValues($expectedArray, $array, $message = '')
    {
        if (!(is_array($expectedArray) || $expectedArray instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'array or ArrayAccess');
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'array or ArrayAccess');
        }

        $constraint = new ArraysHaveEqualValuesConstraint($expectedArray);

        self::assertThat($array, $constraint, $message);
    }

    /**
     * Assert an array's length
     *
     * @param array  $array
     * @param int    $expected
     * @param string $message
     */
    public static function assertArrayLengthEquals($array, $expected, $message = '')
    {
        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'array or ArrayAccess');
        }

        $constraint = new ArrayLengthConstraint($expected, count($array));

        self::assertThat($array, $constraint, $message);
    }

    /**
     * Asserts that an array contains a Model with a known ID
     *
     * @param int|Model $id
     * @param Model[]   $array
     * @param string    $message
     */
    public static function assertArrayContainsModel($id, $array, $message = '')
    {
        if ($id instanceof Model) {
            $id = $id->getId();
        } elseif (!is_int($id)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'array or ArrayAccess');
        }

        foreach ($array as $e) {
            if (!$e instanceof Model) {
                throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'array of models');
            }
        }

        $constraint = new ArrayContainsModelWithIdConstraint($id);

        self::assertThat($array, $constraint, $message);
    }

    /**
     * Asserts that an array contains a Model with a known ID
     *
     * @param int|Model $id
     * @param Model[]   $array
     * @param string    $message
     */
    public static function assertArrayDoesNotContainModel($id, $array, $message = '')
    {
        if ($id instanceof Model) {
            $id = $id->getId();
        } elseif (!is_int($id)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'array or ArrayAccess');
        }

        foreach ($array as $e) {
            if (!$e instanceof Model) {
                throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'array of models');
            }
        }

        $constraint = new ArrayContainsModelWithoutIdConstraint($id);

        self::assertThat($array, $constraint, $message);
    }

    /**
     * Models in unit tests aren't cached. This function explicitly caches a model for the sake of unit tests.
     *
     * @param Model $model
     */
    public static function cacheModel(Model $model)
    {
        if (!Service::getModelCache()) {
            return;
        }

        Service::getModelCache()->save($model);
    }

    /**
     * Wipe all the objects given as parameters
     *
     * @param Model $c,... The object(s) to call the wipe() method on
     */
    protected static function wipe()
    {
        foreach (func_get_args() as $a) {
            if ($a) {
                $a->wipe();
            }
        }
    }

    /**
     * Create a new sample player
     *
     * @return Player
     */
    protected function getNewPlayer()
    {
        ++$this->lastBzid;

        $player = Player::newPlayer($this->lastBzid, sprintf("Sample player %d", ($this->lastBzid - 1)));
        $this->playersCreated[] = $player->getId();

        return $player;
    }

    /**
     * Reset any properties changed on a model that are unrelated to its data
     *
     * @param Model $c,... The object(s) to reset
     */
    protected function reset()
    {
        foreach (func_get_args() as $model) {
            if ($model instanceof BaseModel) {
                $refObject   = new ReflectionObject($model);
                $refProperty = new ReflectionProperty('BaseModel', 'loaded');
                $refProperty->setAccessible(true);
                $refProperty->setValue($model, false);
            }
        }
    }

    protected function setUp()
    {
        self::connectToDatabase();

        $this->faker = Faker\Factory::create();
    }

    /**
     * Clean-up all the database entries added during the test
     */
    public function tearDown()
    {
        foreach ($this->createdModels as $model) {
            $this->wipe($model);
        }

        foreach ($this->playersCreated as $id) {
            self::wipe(Player::get($id));
        }
    }
}
