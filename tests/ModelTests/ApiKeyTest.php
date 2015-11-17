<?php

class ApiKeyTest extends TestCase
{
    /**
     * @var Player
     */
    protected $owner;

    /**
     * @var ApiKey
     */
    protected $key;

    protected function setUp()
    {
        $this->connectToDatabase();

        $this->owner = $this->getNewPlayer();
        $this->key = ApiKey::createKey("Sample Key", $this->owner->getId());
    }

    public function testOwner()
    {
        $key = $this->key;

        $this->assertEquals($this->owner, $key->getOwner());
    }

    public function tearDown()
    {
        $this->key->wipe();
        $this->owner->wipe();
    }
}
