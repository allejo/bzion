<?php

class MatchTest extends TestCase
{
    /** @var Team */
    protected $team_a;
    /** @var Team */
    protected $team_b;
    /** @var Match */
    protected $match;
    /** @var Match */
    protected $match_b;
    /** @var Player */
    protected $player_a;
    /** @var Player */
    protected $player_b;

    protected function setUp()
    {
        $this->connectToDatabase();

        $this->player_a = $this->getNewPlayer();
        $this->player_b = $this->getNewPlayer();

        $this->team_a = Team::createTeam("Team A", $this->player_a->getId(), "", "");
        $this->team_b = Team::createTeam("Team B", $this->player_b->getId(), "", "");
    }

    public function testTeamAWin()
    {
        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 5, 2, 30, $this->player_a->getId());

        $this->assertInstanceOf("Team", $this->match->getTeamA());
        $this->assertInstanceOf("Team", $this->match->getTeamB());

        $this->assertEquals(1225, $this->match->getTeamA()->getElo());
        $this->assertEquals(1175, $this->match->getTeamB()->getElo());

        $this->assertEquals(1225, $this->match->getTeamAEloNew());
        $this->assertEquals(1175, $this->match->getTeamBEloNew());

        $this->assertEquals(1200, $this->match->getTeamAEloOld());
        $this->assertEquals(1200, $this->match->getTeamBEloOLd());

        $this->assertEquals(5, $this->match->getTeamAPoints());
        $this->assertEquals(2, $this->match->getTeamBPoints());

        $this->assertEquals(30, $this->match->getDuration());

        $this->assertEquals(25, $this->match->getEloDiff());

        $this->assertFalse($this->match->isDraw());

        $this->assertEquals($this->team_a->getId(), $this->match->getWinner()->getId());
        $this->assertEquals($this->team_b->getId(), $this->match->getLoser()->getId());
    }

    public function testTeamBWin()
    {
        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 2, 5, 30, $this->player_a->getId());

        $this->assertEquals(1175, $this->match->getTeamAEloNew());
        $this->assertEquals(1225, $this->match->getTeamBEloNew());

        $this->assertEquals(1200, $this->match->getTeamAEloOld());
        $this->assertEquals(1200, $this->match->getTeamBEloOLd());

        $this->assertEquals(2, $this->match->getTeamAPoints());
        $this->assertEquals(5, $this->match->getTeamBPoints());

        $this->assertEquals(25, $this->match->getEloDiff());

        $this->assertFalse($this->match->isDraw());

        $this->assertEquals($this->team_b->getId(), $this->match->getWinner()->getId());
        $this->assertEquals($this->team_a->getId(), $this->match->getLoser()->getId());
    }

    public function testDraw()
    {
        $this->team_a->adjustElo(+10);

        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 3, 3, 30, $this->player_a->getId());

        $this->assertTrue($this->match->isDraw());

        $this->assertEquals(1, $this->match->getEloDiff());

        $this->assertEquals(1209, $this->match->getTeamAEloNew());
        $this->assertEquals(1201, $this->match->getTeamBEloNew());

        $this->assertEquals(1210, $this->match->getTeamAEloOld());
        $this->assertEquals(1200, $this->match->getTeamBEloOLd());

        $this->assertInstanceOf("Team", $this->match->getWinner());
        $this->assertInstanceOf("Team", $this->match->getLoser());
    }

    public function testDrawReverse()
    {
        $this->team_b->adjustElo(+10);

        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 3, 3, 30, $this->player_a->getId());

        $this->assertTrue($this->match->isDraw());

        $this->assertEquals(1, $this->match->getEloDiff());

        $this->assertEquals(1201, $this->match->getTeamAEloNew());
        $this->assertEquals(1209, $this->match->getTeamBEloNew());

        $this->assertEquals(1200, $this->match->getTeamAEloOld());
        $this->assertEquals(1210, $this->match->getTeamBEloOLd());

        $this->assertInstanceOf("Team", $this->match->getWinner());
        $this->assertInstanceOf("Team", $this->match->getLoser());
    }

    public function testEqualEloDraw()
    {
        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 3, 3, 30, $this->player_a->getId());

        $this->assertEquals(0, $this->match->getEloDiff());
        $this->assertEquals($this->match->getTeamAEloNew(), $this->match->getTeamBEloNew());
        $this->assertEquals($this->match->getTeamAEloOld(), $this->match->getTeamBEloOLd());
    }

    public function testShortMatch()
    {
        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 5, 2, 20, $this->player_a->getId());

        $this->assertEquals(20, $this->match->getDuration());

        $this->assertEquals(16, $this->match->getEloDiff());

        $this->assertEquals(1216, $this->match->getTeamAEloNew());
        $this->assertEquals(1184, $this->match->getTeamBEloNew());
    }

    public function testMiscMethods()
    {
        $old_matches = Match::getMatches();

        $this->match = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 5, 2, 30, $this->player_a->getId());
        $this->match_b = Match::enterMatch($this->team_a->getId(), $this->team_b->getId(), 5, 2, 20, $this->player_b->getId());

        $this->assertEquals("now", $this->match->getTimestamp()->diffForHumans());

        $this->assertEquals($this->player_a->getId(), $this->match->getEnteredBy()->getId());

        $this->assertEquals(5, $this->match->getScore($this->team_a->getId()));
        $this->assertEquals(2, $this->match->getOpponentScore($this->team_a->getId()));

        $this->assertEquals($this->team_a->getId(), $this->match->getOpponent($this->team_b->getId())->getId());

        $matches = Match::getMatches();
        $this->assertArrayContainsModel($this->match, $matches);
        $this->assertArrayContainsModel($this->match_b, $matches);
        $this->assertEquals(2, count($matches) - count($old_matches));
    }

    public function testIndividualPlayerEloDoesNotChangeInFunMatch()
    {
        $player_c = $this->getNewPlayer();
        $player_d = $this->getNewPlayer();

        $player_c->adjustElo(300, null);
        $player_d->adjustElo(500, null);

        $this->match = Match::enterMatch(
            null,
            null,
            5,
            1,
            30,
            null,
            'now',
            [$this->player_a->getId(), $this->player_b->getId()],
            [$player_c->getId(), $player_d->getId()],
            null,
            null,
            null,
            Match::FUN
        );

        $this->assertEquals(1200, $this->player_a->getElo());
        $this->assertEquals(1200, $this->player_b->getElo());
        $this->assertEquals(1500, $player_c->getElo());
        $this->assertEquals(1700, $player_d->getElo());
    }

    public function testIndividualPlayerEloChangesInOfficialMatch()
    {
        $player_c = $this->getNewPlayer();
        $player_d = $this->getNewPlayer();

        $this->match = Match::enterMatch(
            $this->team_a->getId(),
            $this->team_b->getId(),
            5,
            1,
            30,
            null,
            'now',
            [$this->player_a->getId(), $this->player_b->getId()],
            [$player_c->getId(), $player_d->getId()]
        );

        $this->assertEquals(1225, $this->player_a->getElo());
        $this->assertEquals(1175, $player_c->getElo());
    }

    /**
     * When a match occurs with mixed team vs mixed team, the ELOs for the teams they belong too should not change but
     * their individual player ELOs should change.
     *
     * - Given Player A (Team A) and Player C (Team B) are the winners in this match, their individual ELOs should
     *   increase
     * - Given Player B (Team B) and Player D (Team A) are the losers in this match, their individual ELOs should
     *   decrease
     * - The ELOs for 'Team A' and 'Team B' should remain unchanged
     */
    public function testEloUpdatesMixedVsMixedOfficialMatch()
    {
        $player_c = $this->getNewPlayer();
        $player_d = $this->getNewPlayer();

        $this->team_a->addMember($player_d->getId());
        $this->team_b->addMember($player_c->getId());

        $this->match = Match::enterMatch(
            null,
            null,
            4,
            3,
            30,
            null,
            'now',
            [$this->player_a->getId(), $player_c->getId()],
            [$this->player_b->getId(), $player_d->getId()],
            null,
            null,
            null,
            Match::OFFICIAL,
            'red',
            'purple'
        );

        $this->assertGreaterThan(1200, $this->player_a->getElo());
        $this->assertGreaterThan(1200, $player_c->getElo());

        $this->assertLessThan(1200, $this->player_b->getElo());
        $this->assertLessThan(1200, $player_d->getElo());

        $this->assertEquals(1200, $this->team_a->getElo());
        $this->assertEquals(1200, $this->team_b->getElo());

        $this->assertEquals('red', $this->match->getTeamA()->getId());
        $this->assertEquals('purple', $this->match->getTeamB()->getId());
    }

    /**
     * When a match occurs with a team vs a mixed team, the ELOs for the complete team should change and the individual
     * ELOs for the participants should change.
     *
     * - Given Player A and Player C both belong to 'Team A' and lose a match, their individual ELOs should decrease and
     *   the ELO of their Team should decrease.
     * - Given Player B (Team B) and Player D (Teamless) win the match, their individual ELOs should increase but the ELO
     *   for Team B should remain unchanged
     */
    public function testEloUpdatesTeamVsMixedOfficialMatch()
    {
        $player_c = $this->getNewPlayer();
        $player_d = $this->getNewPlayer();

        $this->team_a->addMember($player_c->getId());

        $this->match = Match::enterMatch(
            $this->team_a->getId(),
            null,
            3,
            4,
            30,
            null,
            'now',
            [$this->player_a->getId(), $player_c->getId()],
            [$this->player_b->getId(), $player_d->getId()],
            null,
            null,
            null,
            Match::OFFICIAL,
            'red',
            'purple'
        );

        $this->assertTrue($player_d->isTeamless());

        $this->assertEquals(1175, $this->team_a->getElo());
        $this->assertEquals(1200, $this->team_b->getElo());

        $this->assertGreaterThan(1200, $this->player_b->getElo());
        $this->assertGreaterThan(1200, $player_d->getElo());

        $this->assertLessThan(1200, $this->player_a->getElo());
        $this->assertLessThan(1200, $player_c->getElo());

        $this->assertInstanceOf(Team::class, $this->match->getTeamA());
        $this->assertInstanceOf(ColorTeam::class, $this->match->getTeamB());
    }

    public function testGetTeamMatchTypeIsTeamVsTeam()
    {
        $this->match = Match::enterMatch(
            $this->team_a->getId(),
            $this->team_b->getId(),
            0,
            0,
            20,
            $this->player_a->getId()
        );

        $this->assertEquals(Match::TEAM_V_TEAM, $this->match->getTeamMatchType());
    }

    public function testGetTeamMatchTypeIsTeamVsMixed()
    {
        $this->match = Match::enterMatch(
            $this->team_a->getId(),
            null,
            0,
            0,
            30,
            $this->player_a->getId(),
            'now',
            [],
            [$this->player_b->getId()]
        );

        $this->assertEquals(Match::TEAM_V_MIXED, $this->match->getTeamMatchType());
    }

    public function testGetTeamMatchTypeIsMixedVsMixed()
    {
        $this->match = Match::enterMatch(
            null,
            null,
            0,
            0,
            30,
            $this->player_a->getId(),
            'now',
            [$this->player_a->getId()],
            [$this->player_b->getId()]
        );

        $this->assertEquals(Match::MIXED_V_MIXED, $this->match->getTeamMatchType());
    }

    public function testEnterMatchWithColorTeamAndNoPlayerRoster()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->match = Match::enterMatch(
            $this->team_a->getId(),
            null,
            0,
            0,
            30,
            $this->player_a->getId(),
            'now',
            [],
            []
        );
    }

    public function tearDown()
    {
        $this->wipe($this->match, $this->match_b, $this->team_a, $this->team_b);
        parent::tearDown();
    }
}
