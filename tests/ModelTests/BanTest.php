<?php

class BanTest extends TestCase
{
    protected function setUp()
    {
        $this->connectToDatabase();
    }

    public function testAutomaticExpiry()
    {
        $victim = $this->getNewPlayer();
        $ban    = Ban::addBan($victim->getId(), $this->getNewPlayer()->getId(), TimeDate::now()->addDay(), "Reason");

        $this->assertTrue($victim->isBanned());
        $this->assertEquals(Ban::getBan($victim->getId())->getId(), $ban->getId());

        $ban->setExpiration(TimeDate::now()->subDay());

        $this->assertFalse($victim->isBanned());
        $this->assertNull(Ban::getBan($victim->getId()));
    }

    public function testIndefiniteExpiry()
    {
        $victim = $this->getNewPlayer();
        $ban    = Ban::addBan($victim->getId(), $this->getNewPlayer()->getId(), null, "Reason");

        $this->assertTrue($victim->isBanned());
        $this->assertEquals(Ban::getBan($victim->getId())->getId(), $ban->getId());
    }
}
