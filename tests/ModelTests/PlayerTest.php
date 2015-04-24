<?php

class PlayerTest extends TestCase
{
    /**
     * @var Player
     */
    private $player;

    protected function setUp()
    {
        $this->connectToDatabase();

        $this->player = $this->getNewPlayer();
    }

    public function testRoleModification()
    {
        $this->markTestSkipped("Skip a segfaulting test?");
        $role = new Role(Role::ADMINISTRATOR);
        $this->assertTrue($role->hasPerm(Permission::PUBLISH_NEWS));
        $this->assertFalse($this->player->hasPermission(Permission::PUBLISH_NEWS));

        $this->player->addRole($role);
        $this->assertTrue($this->player->hasPermission(Permission::PUBLISH_NEWS));
    }
}
