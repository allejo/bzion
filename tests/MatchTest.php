<?php

class MatchTest extends TestCase{

     protected function setUp()
    {
        global $db;
        $db = new Database();

    }

    public function testTeamAWin(){
        $team_a = Team::createTeam("Team A", 49434, "", "");
        $team_b = Team::createTeam("Team B", 49434, "", "");

        $match = Match::enterMatch($team_a->getId(), $team_b->getId(), 5, 2, 30, 49434);

        $this->assertInstanceOf("Team", $match->getTeamA());
        $this->assertInstanceOf("Team", $match->getTeamB());

        $this->assertEquals(1225, $match->getTeamA()->getElo());
        $this->assertEquals(1175, $match->getTeamB()->getElo());

        $this->assertEquals(1225, $match->getTeamAEloNew());
        $this->assertEquals(1175, $match->getTeamBEloNew());

        $this->assertEquals(5, $match->getTeamAPoints());
        $this->assertEquals(2, $match->getTeamBPoints());

        $this->assertEquals(30, $match->getDuration());

        $this->assertEquals(25, $match->getEloDiff());

        $this->wipe($team_a, $team_b, $match);
    }

    public function testTeamBWin(){
        $team_a = Team::createTeam("Team A", 49434, "", "");
        $team_b = Team::createTeam("Team B", 49434, "", "");

        $match = Match::enterMatch($team_a->getId(), $team_b->getId(), 2, 5, 30, 49434);

        $this->assertEquals(1175, $match->getTeamAEloNew());
        $this->assertEquals(1225, $match->getTeamBEloNew());

        $this->assertEquals(2, $match->getTeamAPoints());
        $this->assertEquals(5, $match->getTeamBPoints());

        $this->assertEquals(25, $match->getEloDiff());

        $this->wipe($team_a, $team_b, $match);
    }


    public function testDraw(){
        $team_a = Team::createTeam("Team A", 49434, "", "");
        $team_b = Team::createTeam("Team B", 49434, "", "");

        $team_a->changeElo(+10);

        $match = Match::enterMatch($team_a->getId(), $team_b->getId(), 3, 3, 30, 49434);

        $this->assertEquals(1, $match->getEloDiff());

        $this->assertEquals(1209, $match->getTeamAEloNew());
        $this->assertEquals(1201, $match->getTeamBEloNew());

        $this->wipe($team_a, $team_b, $match);
    }

    public function testEqualEloDraw() {
        $team_a = Team::createTeam("Team A", 49434, "", "");
        $team_b = Team::createTeam("Team B", 49434, "", "");

        $match = Match::enterMatch($team_a->getId(), $team_b->getId(), 3, 3, 30, 49434);

        $this->assertEquals(0, $match->getEloDiff());
        $this->assertEquals($match->getTeamAEloNew(), $match->getTeamBEloNew());

        $this->wipe($team_a, $team_b, $match);
    }

    public function testShortMatch(){
        $team_a = Team::createTeam("Team A", 49434, "", "");
        $team_b = Team::createTeam("Team B", 49434, "", "");

        $match = Match::enterMatch($team_a->getId(), $team_b->getId(), 5, 2, 20, 49434);

        $this->assertEquals(20, $match->getDuration());

        $this->assertEquals(16, $match->getEloDiff());

        $this->assertEquals(1216, $match->getTeamAEloNew());
        $this->assertEquals(1184, $match->getTeamBEloNew());

        $this->wipe($team_a, $team_b, $match);
    }

}