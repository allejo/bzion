<?php

class MatchTest extends TestCase {
    protected $team_a;
    protected $team_b;
    protected $match;
    protected $match_b;

    protected function setUp() {
        global $db;
        $db = new Database();

        $this->team_a = Team::createTeam("Team A", 49434, "", "");
        $this->team_b = Team::createTeam("Team B", 49434, "", "");
    }

    public function testTeamAWin() {
        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 5, 2, 30, 49434);

        $this->assertInstanceOf("Team", $this->match->getTeamA());
        $this->assertInstanceOf("Team", $this->match->getTeamB());

        $this->assertEquals(1225, $this->match->getTeamA()->getElo());
        $this->assertEquals(1175, $this->match->getTeamB()->getElo());

        $this->assertEquals(1225, $this->match->getTeamAEloNew());
        $this->assertEquals(1175, $this->match->getTeamBEloNew());

        $this->assertEquals(5, $this->match->getTeamAPoints());
        $this->assertEquals(2, $this->match->getTeamBPoints());

        $this->assertEquals(30, $this->match->getDuration());

        $this->assertEquals(25, $this->match->getEloDiff());
    }

    public function testTeamBWin() {
        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 2, 5, 30, 49434);

        $this->assertEquals(1175, $this->match->getTeamAEloNew());
        $this->assertEquals(1225, $this->match->getTeamBEloNew());

        $this->assertEquals(2, $this->match->getTeamAPoints());
        $this->assertEquals(5, $this->match->getTeamBPoints());

        $this->assertEquals(25, $this->match->getEloDiff());
    }

    public function testDraw() {
        $this->team_a->changeElo(+10);

        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 3, 3, 30, 49434);

        $this->assertEquals(1, $this->match->getEloDiff());

        $this->assertEquals(1209, $this->match->getTeamAEloNew());
        $this->assertEquals(1201, $this->match->getTeamBEloNew());
    }

    public function testEqualEloDraw() {
        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 3, 3, 30, 49434);

        $this->assertEquals(0, $this->match->getEloDiff());
        $this->assertEquals($this->match->getTeamAEloNew(), $this->match->getTeamBEloNew());
    }

    public function testShortMatch() {
        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 5, 2, 20, 49434);

        $this->assertEquals(20, $this->match->getDuration());

        $this->assertEquals(16, $this->match->getEloDiff());

        $this->assertEquals(1216, $this->match->getTeamAEloNew());
        $this->assertEquals(1184, $this->match->getTeamBEloNew());
    }

    public function testMiscMethods() {
        $old_matches = Match::getMatches('id');

        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 5, 2, 30, 49434);
        $this->match_b = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 5, 2, 20, 49434);

        $this->assertEquals("now", $this->match->getTimestamp());

        $this->assertEquals(49434, $this->match->getEnteredBy());

        $matches = Match::getMatches('id');
        $this->assertContains($this->match->getId(), $matches);
        $this->assertContains($this->match_b->getId(), $matches);
        $this->assertEquals(2, count($matches) - count($old_matches));

    }

    public function tearDown() {
        $this->wipe($this->team_a, $this->team_b, $this->match, $this->match_b);
    }
}
