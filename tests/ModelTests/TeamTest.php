<?php

class TeamTest extends TestCase {

    private $player;
    private $bzid;
    private $team;

    protected function setUp() {
        global $db;
        $db = new Database();

        $this->player = $this->getNewPlayer();
        $this->bzid   = $this->player->getBZID();
    }

    public function testTeamLeader() {
        $this->team = Team::createTeam("Sample Team", $this->bzid, "Sample Avatar Text", "Sample Description");

        $team = new Team($this->team->getId());

        $this->assertEquals($this->player->getId(), $team->getLeader()->getId());
    }

    public function testTeamName() {
        $this->team = Team::createTeam("Team name test", $this->bzid, "Avatar", "Description");

        $team = new Team($this->team->getId());

        $this->assertEquals("Team name test", $team->getName());
        $this->assertEquals("team-name-test", $team->getAlias());

        $this->assertEquals($this->team->getId(), Team::getFromAlias("team-name-test")->getId());
    }

    public function testIrrationalTeamName() {
        $this->team = Team::createTeam("13435", $this->bzid, "Avatar", "Description");

        $team = new Team($this->team->getId());

        $this->assertEquals("13435", $team->getName());
        $this->assertEquals("13435-", $team->getAlias());

        $this->assertEquals($this->team->getId(), Team::getFromAlias("13435-")->getId());
    }

    public function testIrrationalTeamName2() {
        $this->team = Team::createTeam("-()#*$%!", $this->bzid, "Avatar", "Description");

        $team = new Team($this->team->getId());

        $this->assertEquals("-()#*$%!", $team->getName());
        $this->assertEquals($this->team->getId(), $team->getAlias());

        $this->assertEquals($this->team->getId(), Team::getFromAlias($team->getId())->getId());
    }

    public function testMembers() {
        $this->team = Team::createTeam("Sample Team", $this->bzid, "Avatar", "Description");
        $extraMember = $this->getNewPlayer();
        $otherPlayer = $this->getNewPlayer();

        $this->team->addMember($otherPlayer->getBZID());
        $this->team->addMember($extraMember->getBZID());
        $this->team->removeMember($otherPlayer->getBZID());

        $team = new Team($this->team->getId());

        $members = $team->getMembers('bzid');
        $expectedMembers = array($this->player->getBZID(), $extraMember->getBZID());

        $this->assertEquals(2, $team->getNumMembers());
        $this->assertArraysHaveEqualValues($expectedMembers, $members);

        $this->wipe($extraMember, $otherPlayer);
    }

    public function testMatches() {
        $this->team = Team::createTeam("Sample Team", $this->bzid, "Avatar", "Description");
        $otherPlayer = $this->getNewPlayer();
        $otherTeam  = Team::createTeam("Sample Team 2", $otherPlayer->getBZID(), "Avatar", "Description");

        $match_a = Match::enterMatch($this->team->getId(), $otherTeam->getId(), 5, 2, 30, 49434);
        $match_b = Match::enterMatch($this->team->getId(), $otherTeam->getId(), 5, 2, 20, 49434);

        $team = new Team($this->team->getId());

        $this->assertEquals(2, $team->getNumTotalMatches());
        $this->assertArraysHaveEqualValues(array($match_a->getId(), $match_b->getId()), $team->getMatches());

        $this->wipe($otherPlayer, $otherTeam, $match_a, $match_b);
    }

    public function testMiscMethods() {
        $this->team = Team::createTeam("Sample Team", $this->bzid, "Avatar", "Description");

        $team = new Team($this->team->getId());

        $this->assertEquals("now", $team->getCreationDate());
    }

    public function tearDown() {
        parent::tearDown();

        $this->wipe($this->team);
    }
}
