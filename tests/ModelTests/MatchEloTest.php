<?php

class MatchEloTest extends TestCase
{
    /** @var \Faker\Generator */
    private $faker;
    private $createdModels = [];

    protected function setUp()
    {
        $this->connectToDatabase();

        $this->faker = Faker\Factory::create();
    }

    public function tearDown()
    {
        foreach ($this->createdModels as $model)
        {
            $this->wipe($model);
        }

        parent::tearDown();
    }

    // Yes, we need a test for our own helper function in our tests
    public function testGenerateMatches()
    {
        $matchCount = 8;

        $team = null;
        $matches = [];

        $this->generateMatches($matchCount, $team, $matches);

        $this->assertArrayLengthEquals($matches, $matchCount);
        $this->assertInstanceOf(Team::class, $team);
        $this->assertInstanceOf(Match::class, $matches[0]);
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

    // Onward to our real tests for Elo recalculations!
    public function testEloRecalculation()
    {
        /** @var Team $team */
        $team = null;
        /** @var Match[] $matches */
        $matches = [];

        $this->generateMatches(10, $team, $matches);

        $preRecalcTeamElo = $team->getElo();
        $preRecalcLeaderElo = $team->getLeader()->getElo();

        $matchToEdit = $matches[3];

        $scoreTeamA = $matchToEdit->getScore($matchToEdit->getTeamA());
        $scoreTeamB = $matchToEdit->getScore($matchToEdit->getTeamB());

        $matchToEdit->setTeamPoints($scoreTeamB, $scoreTeamA);

        ob_start();
        Match::recalculateMatchesSince($matchToEdit);
        ob_end_clean();

        $newLeaderElo = Player::get($team->getLeader())->getElo();
        $newTeamElo = Team::get($team->getId())->getElo();

        $this->assertNotEquals($preRecalcLeaderElo, $newLeaderElo);
        $this->assertNotEquals($preRecalcTeamElo, $newTeamElo);
    }

    /**
     * Generate random matches with a single team always being in the match.
     *
     * @param int $numberOfMatches
     * @param Team $team_a
     * @param Match[] $matches
     *
     * @throws Exception
     */
    private function generateMatches($numberOfMatches, &$team_a, array &$matches)
    {
        $participantCount = $this->faker->numberBetween(2, 10);

        $team_a = $team_b = null;
        $team_a_players = $team_b_players = [];

        $this->generateTeamWithPlayers($participantCount, $team_a, $team_a_players);
        $this->generateTeamWithPlayers($participantCount, $team_b, $team_b_players);

        $team_a_roster = __::map($team_a_players, function ($n) { return $n->getId(); });
        $team_b_roster = __::map($team_b_players, function ($n) { return $n->getId(); });

        for ($i = 0; $i < $numberOfMatches; $i++) {
            $matches[$i] = Match::enterMatch(
                $team_a->getId(),
                ($this->faker->boolean) ? $team_b : null,
                $this->faker->numberBetween(0, 6),
                $this->faker->numberBetween(0, 6),
                $this->faker->randomElement([15, 20, 30]),
                null,
                sprintf('-%d days', $numberOfMatches - $i),
                $team_a_roster,
                $team_b_roster,
                null,
                null,
                null,
                'official',
                'red',
                'purple'
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
