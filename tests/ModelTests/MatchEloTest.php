<?php

class MatchEloTest extends TestCase
{
    const TEAM_LOSS = 0;
    const TEAM_WIN = 1;
    const TEAM_DRAW = 2;

    //
    // Yes, we need to test our own helper function in our tests
    //

    public function testGenerateMatchesWithNoRiggedMatches()
    {
        $matchCount = 8;

        $team = null;
        $matches = [];

        $this->generateMatches($matchCount, $team, $matches);

        $this->assertArrayLengthEquals($matches, $matchCount);
        $this->assertInstanceOf(Team::class, $team);
        $this->assertInstanceOf(Match::class, $matches[0]);
    }

    public function testGenerateMatchesWithRiggedWin()
    {
        /**
         * @var Match[] $matches
         */
        $team = null;
        $matches = [];
        $riggedMatch = 3;

        $this->generateMatches(5, $team, $matches, $riggedMatch, self::TEAM_WIN);

        $this->assertGreaterThan($matches[$riggedMatch]->getTeamBPoints(), $matches[$riggedMatch]->getTeamAPoints());
    }

    public function testGenerateMatchesWithRiggedLoss()
    {
        /**
         * @var Match[] $matches
         */
        $team = null;
        $matches = [];
        $riggedMatch = 2;

        $this->generateMatches(5, $team, $matches, $riggedMatch, self::TEAM_LOSS);

        $this->assertGreaterThan($matches[$riggedMatch]->getTeamAPoints(), $matches[$riggedMatch]->getTeamBPoints());
    }

    public function testGenerateMatchesWithRiggedDraw()
    {
        /**
         * @var Match[] $matches
         */
        $team = null;
        $matches = [];
        $riggedMatch = 4;

        $this->generateMatches(5, $team, $matches, $riggedMatch, self::TEAM_DRAW);

        $this->assertEquals($matches[$riggedMatch]->getTeamBPoints(), $matches[$riggedMatch]->getTeamAPoints());
    }

    public function testGenerateTeamWithPlayers()
    {
        $playerCount = 5;

        $team = null;
        $players = [];

        $this->generateTeamWithPlayers($playerCount, $team, $players);

        $this->assertArrayLengthEquals($players, $playerCount);
        $this->assertInstanceOf(Team::class, $team);
        $this->assertInstanceOf(Player::class, $players[0]);
    }

    //
    // Onward to our real tests for Elo recalculations!
    //

    public static function eloRecalculationsProvider()
    {
        return [
            [ self::TEAM_DRAW, 'assertEquals' ],
            [ self::TEAM_WIN,  'assertLessThan' ],
            [ self::TEAM_LOSS, 'assertGreaterThan' ],
        ];
    }

    /**
     * @dataProvider eloRecalculationsProvider
     *
     * @param int    $riggedResult
     * @param string $functionCall
     *
     * @see MatchEloTest::TEAM_LOSS
     * @see MatchEloTest::TEAM_WIN
     * @see MatchEloTest::TEAM_DRAW
     *
     * @throws Exception
     */
    public function testEloRecalculationOnSituation($riggedResult, $functionCall)
    {
        /**
         * @var Team $team
         * @var Match[] $matches
         */
        $team = null;
        $matches = [];
        $riggedID = $this->faker->numberBetween(0, 9);

        $this->generateMatches(10, $team, $matches, $riggedID, $riggedResult);

        // The match we'll be editing in this recalculation test
        $matchToEdit = $matches[$riggedID];

        // We need a random participant within the match to confirm that a player's Elo get updated
        $matchParticipant = $matchToEdit->getPlayers($team)[0];

        // Get values for the Match prior to edits being made
        $preRecalcTeamElo = $matchToEdit->getTeamAEloNew();
        $preRecalcPlayerEloBefore = $matchToEdit->getPlayerEloBefore($matchParticipant);
        $preRecalcPlayerEloAfter  = $matchToEdit->getPlayerEloAfter($matchParticipant);
        $preRecalcTeamEloDiff   = $matchToEdit->getEloDiff(false);
        $preRecalcPlayerEloDiff = $matchToEdit->getPlayerEloDiff(false);

        // Swap the team scores
        $scoreTeamA = $matchToEdit->getScore($matchToEdit->getTeamA());
        $scoreTeamB = $matchToEdit->getScore($matchToEdit->getTeamB());
        $matchToEdit->setTeamPoints($scoreTeamB, $scoreTeamA);

        ob_start();
        Match::recalculateMatchesSince($matchToEdit);
        ob_end_clean();

        // Forcefully get an updated object to bypass our cache
        $matchRefreshed = Match::get($matchToEdit->getId());

        // Get values for the Match *after* the edits have been made
        $postRecalcTeamElo = $matchRefreshed->getTeamAEloNew();
        $postRecalcPlayerEloBefore = $matchRefreshed->getPlayerEloBefore($matchParticipant);
        $postRecalcPlayerEloAfter  = $matchRefreshed->getPlayerEloAfter($matchParticipant);
        $postRecalcTeamEloDiff = $matchRefreshed->getEloDiff(false);
        $postRecalcPlayerEloDiff = $matchRefreshed->getPlayerEloDiff(false);

        // Recalculations don't touch matches prior to this, so the values should be identical
        $this->assertEquals($preRecalcPlayerEloBefore, $postRecalcPlayerEloBefore);

        // Custom Assertions
        $this->{$functionCall}($preRecalcTeamElo,        $postRecalcTeamElo);
        $this->{$functionCall}($preRecalcPlayerEloAfter, $postRecalcPlayerEloAfter);
        $this->{$functionCall}($preRecalcTeamEloDiff,    $postRecalcTeamEloDiff);
        $this->{$functionCall}($preRecalcPlayerEloDiff,  $postRecalcPlayerEloDiff);
    }

    /**
     * Generate random matches with a single team always being in the match.
     *
     * @param int     $numberOfMatches
     * @param Team    $team_a
     * @param Match[] $matches
     * @param int     $riggedMatchNumber
     * @param int     $riggedResult
     *
     * @throws Exception
     */
    private function generateMatches($numberOfMatches, &$team_a, array &$matches, $riggedMatchNumber = -1, $riggedResult = self::TEAM_LOSS)
    {
        $team_a = $team_b = null;
        $team_a_players = $team_b_players = [];
        $fullRosterCount = $this->faker->numberBetween(4, 15);

        $this->generateTeamWithPlayers($fullRosterCount, $team_a, $team_a_players);
        $this->generateTeamWithPlayers($fullRosterCount, $team_b, $team_b_players);

        $participantCount = $this->faker->numberBetween(2, $fullRosterCount - 2);

        for ($i = 0; $i < $numberOfMatches; $i++) {
            // Get only *some* of the players from each team to play in the matches
            $team_a_roster = __::map($this->faker->randomElements($team_a_players, $participantCount), function ($n) { return $n->getId(); });
            $team_b_roster = __::map($this->faker->randomElements($team_b_players, $participantCount), function ($n) { return $n->getId(); });

            $team_a_score = $this->faker->numberBetween(0, 6);
            $team_b_score = $this->faker->numberBetween(0, 6);

            if ($riggedMatchNumber === $i) {
                switch ($riggedResult) {
                    case self::TEAM_LOSS:
                        $team_a_score = 0;
                        $team_b_score = $this->faker->numberBetween(1, 6);
                        break;

                    case self::TEAM_WIN:
                        $team_a_score = $this->faker->numberBetween(1, 6);
                        $team_b_score = 0;
                        break;

                    case self::TEAM_DRAW:
                        $team_a_score = $team_b_score = $this->faker->numberBetween(1, 6);
                        break;
                }
            }

            $matches[$i] = Match::enterMatch(
                $team_a->getId(),
                ($this->faker->boolean) ? $team_b : null,
                $team_a_score,
                $team_b_score,
                $this->faker->randomElement([15, 20, 30]),
                null,
                sprintf('-%d days', $numberOfMatches - $i),
                $team_a_roster,
                $team_b_roster
            );

            array_unshift($this->createdModels, $matches[$i]);
        }
    }

    /**
     * Generate a real team with multiple players assigned to the team.
     *
     * @param int $playerCount
     * @param Team $team
     * @param Player[] $players
     *
     * @throws Exception
     */
    private function generateTeamWithPlayers($playerCount, &$team, array &$players)
    {
        $players[0] = $this->getNewPlayer();

        $team = Team::createTeam($this->faker->text($maxNbChars = 42), $players[0]->getId(), '', '');

        array_unshift($this->createdModels, $team);

        for ($i = 1; $i < $playerCount; $i++) {
            $players[$i] = $this->getNewPlayer();
            $team->addMember($players[$i]->getId());
        }
    }
}
