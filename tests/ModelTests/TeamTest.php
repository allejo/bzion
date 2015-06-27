<?php

class TeamTest extends TestCase
{
    /**
     * @var Player
     */
    private $player;

    /**
     * @var Team
     */
    private $team;

    protected function setUp()
    {
        $this->connectToDatabase();

        $this->player = $this->getNewPlayer();
    }

    public function testTeamLeader()
    {
        $this->team = Team::createTeam("Sample Team", $this->player->getId(), "Sample Avatar Text", "Sample Description");
        $team = Team::get($this->team->getId());

        $this->assertEquals($this->player->getId(), $team->getLeader()->getId());
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidTeamLeader()
    {
        $this->team = Team::createTeam("An Invalid Team", 0, "Sample Avatar Text", "Team Number 1 sample description");
    }

    public function testTeamName()
    {
        $this->team = Team::createTeam("Team name test", $this->player->getId(), "Avatar", "Description");
        $team = Team::get($this->team->getId());

        $this->assertEquals("Team name test", $team->getName());
    }

    public function testTeamAlias()
    {
        $this->team = Team::createTeam("Team name test", $this->player->getId(), "Avatar", "Description");
        $team = Team::get($this->team->getId());

        $this->assertEquals("team-name-test", $team->getAlias());
    }

    public function testTeamFetchByAlias()
    {
        $this->team = Team::createTeam("Team name test", $this->player->getId(), "Avatar", "Description");

        $this->assertEquals($this->team->getId(), Team::fetchFromAlias("team-name-test")->getId());
    }

    public function testIrrationalTeamName()
    {
        $this->team = Team::createTeam("13435", $this->player->getId(), "Avatar", "Description");

        $team = Team::get($this->team->getId());

        $this->assertEquals("13435", $team->getName());
        $this->assertEquals("13435-", $team->getAlias());

        $this->assertEquals($this->team->getId(), Team::fetchFromAlias("13435-")->getId());
        $this->assertEquals($this->team->getId(),  Team::fetchFromSlug("13435-")->getId());
    }

    public function testIrrationalTeamName2()
    {
        $this->team = Team::createTeam("-()#*$%!", $this->player->getId(), "Avatar", "Description");

        $team = Team::get($this->team->getId());

        $this->assertEquals("-()#*$%!", $team->getName());
        $this->assertEquals($this->team->getId(), $team->getAlias());

        $this->assertEquals($this->team->getId(), Team::fetchFromSlug($team->getId())->getId());
    }

    public function testDisallowedAlias()
    {
        $this->team = Team::createTeam("new", $this->player->getId(), "Avatar", "Description");

        $this->assertNotEquals("new", $this->team->getAlias());
    }

    public function testMembers()
    {
        $this->team = Team::createTeam("Sample Team", $this->player->getId(), "Avatar", "Description");
        $extraMember = $this->getNewPlayer();
        $otherPlayer = $this->getNewPlayer();

        $this->team->addMember($otherPlayer->getId());
        $this->team->addMember($extraMember->getId());
        $this->team->removeMember($otherPlayer->getId());

        $team = Team::get($this->team->getId());

        $members = $team->getMembers('id');
        $expectedMembers = array($this->player, $extraMember);

        $this->assertEquals(2, $team->getNumMembers());
        $this->assertArraysHaveEqualValues($expectedMembers, $members);

        $this->wipe($extraMember, $otherPlayer);
    }

    public function testMatches()
    {
        $this->team = Team::createTeam("Sample Team", $this->player->getId(), "Avatar", "Description");
        $otherPlayer = $this->getNewPlayer();
        $otherTeam  = Team::createTeam("Sample Team 2", $otherPlayer->getId(), "Avatar", "Description");

        $match_a = Match::enterMatch($this->team->getId(), $otherTeam->getId(), 5, 2, 30, $this->player->getId());
        $match_b = Match::enterMatch($this->team->getId(), $otherTeam->getId(), 5, 2, 20, $this->player->getId());

        $team = Team::get($this->team->getId());

        $this->assertEquals(2, $team->getNumTotalMatches());
        $this->assertArraysHaveEqualValues(array($match_a, $match_b), $team->getMatches());

        $this->wipe($match_a, $match_b, $otherTeam, $otherPlayer);
    }

    public function testMiscMethods()
    {
        $this->team = Team::createTeam("Sample Team", $this->player->getId(), "Avatar", "Description");

        $team = Team::get($this->team->getId());

        $this->assertEquals("now", $team->getCreationDate());
    }

    public function tearDown()
    {
        $this->wipe($this->team);
        parent::tearDown();
    }
}
