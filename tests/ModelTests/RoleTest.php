<?php

class RoleTest extends TestCase
{
    protected $player_a;
    protected $player_b;
    protected $leaderRole;
    protected $normalRole;

    protected function setUp()
    {
        $this->connectToDatabase();

        $this->player_a = $this->getNewPlayer();
        $this->player_b = $this->getNewPlayer();

        $this->leaderRole = Role::createNewRole("Sample Leader Role", true, true, "fa-gavel", "blue", "Sample Leaders", 1);
        $this->normalRole = Role::createNewRole("Sample Normal Role", true, false);
    }

    public function testLeaderRole()
    {
        $role = Role::get($this->leaderRole->getId());

        $this->player_a->addRole($role->getId());
        $this->player_b->addRole($role->getId());

        $this->assertTrue($role->isReusable());
        $this->assertTrue($role->displayAsLeader());
        $this->assertFalse($role->isProtected());

        $this->assertEquals("Sample Leader Role", $role->getName());
        $this->assertEquals("fa-gavel", $role->getDisplayIcon());
        $this->assertEquals("blue", $role->getDisplayColor());
        $this->assertEquals("Sample Leaders", $role->getDisplayName());
        $this->assertEquals(1, $role->getDisplayOrder());

        $this->assertArrayContainsModel($this->player_a, $role->getUsers());

        $this->assertFalse($role->addPerm("some_permission_that_does_not_exist"));

        $this->assertTrue($role->addPerm("add_team"));
        $this->assertFalse($role->addPerm("add_team"));
        $this->assertTrue($role->hasPerm("add_team"));

        $this->assertArrayHasKey("add_team", $role->getPerms());

        $this->assertTrue($role->removePerm("add_team"));
        $this->assertFalse($role->removePerm("add_team"));
        $this->assertNotTrue($role->hasPerm("add_team"));

        $this->assertArrayNotHasKey("add_team", $role->getPerms());

        $this->assertArrayContainsModel($role, Role::getLeaderRoles());

        $this->wipe($role);
    }

    public function testNormalRole()
    {
        $role = Role::get($this->normalRole->getId());

        $this->player_b->addRole($role->getId());

        $this->assertFalse($role->displayAsLeader());
        $this->assertFalse($role->isProtected());

        $this->assertEquals("Sample Normal Role", $role->getName());

        $this->assertArrayContainsModel($role, Role::getRoles($this->player_b->getId()));

        $this->wipe($role);
    }

    public function tearDown()
    {
        $this->wipe($this->player_a, $this->player_b, $this->leaderRole, $this->normalRole);
        parent::tearDown();
    }
}
