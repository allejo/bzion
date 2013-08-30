<?php

class MatchTest extends PHPUnit_Framework_TestCase{

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

        $this->assertEquals(1245, $match->getTeamA()->getElo());
        $this->assertEquals(1155, $match->getTeamB()->getElo());

        $this->assertEquals(1245, $match->getTeamAEloNew());
        $this->assertEquals(1155, $match->getTeamBEloNew());

        $this->assertEquals(5, $match->getTeamAPoints());
        $this->assertEquals(2, $match->getTeamBPoints());

        $this->assertEquals(30, $match->getDuration());

        $this->assertEquals(45, $match->getEloDiff());
    }

    public function testTeamBWin(){
        $team_a = Team::createTeam("Team A", 49434, "", "");
        $team_b = Team::createTeam("Team B", 49434, "", "");

        $match = Match::enterMatch($team_a->getId(), $team_b->getId(), 2, 5, 30, 49434);

        $this->assertEquals(1155, $match->getTeamAEloNew());
        $this->assertEquals(1245, $match->getTeamBEloNew());

        $this->assertEquals(2, $match->getTeamAPoints());
        $this->assertEquals(5, $match->getTeamBPoints());

        $this->assertEquals(45, $match->getEloDiff());
    }


    public function testDraw(){
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $team_a = Team::createTeam("Team A", 49434, "", "");
        $team_b = Team::createTeam("Team B", 49434, "", "");

        $match = Match::enterMatch($team_a->getId(), $team_b->getId(), 3, 3, 30, 49434);
    }

    public function testShortMatch(){
        $team_a = Team::createTeam("Team A", 49434, "", "");
        $team_b = Team::createTeam("Team B", 49434, "", "");

        $match = Match::enterMatch($team_a->getId(), $team_b->getId(), 5, 2, 20, 49434);

        $this->assertEquals(20, $match->getDuration());

        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

}