<?php

class CacheTest extends TestCase
{
    private $player;
    private $notification;

    protected function setUp()
    {
        $this->connectToDatabase();
        $this->player = $this->getNewPlayer();
    }

    public function testEmptyModelCache()
    {
        $cache = new ModelCache();

        $this->assertFalse($cache->has('Player', 15));
        $this->assertNull($cache->get('Player', 15));
        $this->assertEquals(48, $cache->get('Player', 15, 48));
    }

    public function testModelCache()
    {
        $cache = new ModelCache();
        $cache->save($this->player);
        $id = $this->player->getId();

        $this->assertTrue($cache->has('Player', $id));
        $this->assertInstanceOf('Player', $cache->get('Player', $id));
        $this->assertInstanceOf('Player', $cache->get('Player', $id, 48));
        $this->assertEquals($this->player, $cache->get('Player', $id));
    }
}
