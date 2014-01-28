<?php

class TeamTest extends TestCase {

    private $player;
    private $bzid;
    private $team;

    protected function setUp() {
        global $db;
        $db = new Database();

        $this->bzid = 180;
        $this->player = Player::newPlayer($this->bzid, "testingPlayer");
    }

    public function testTeamLeader() {
        $this->team = Team::createTeam("Sample Team", $this->bzid, "Sample Avatar Text", "Sample Description");

        $team = new Team($this->team->getId());

        $this->assertEquals($this->player->getId(), $team->getLeader()->getId());
    }

    public function testTeamName() {
        $this->team = Team::createTeam("Team name test", $this->bzid, "Avatar", "Description");

        $team = new Team($this->team->getId());

        $this->assertEquals("team-name-test", $team->getAlias());
    }

    public function testIrrationalTeamName() {
        $this->team = Team::createTeam("-()#*$%!", $this->bzid, "Avatar", "Description");

        $team = new Team($this->team->getId());

        $this->assertEquals($this->team->getId(), $team->getAlias());
    }

    public function tearDown() {
        $this->wipe($this->player, $this->team);
    }
}
