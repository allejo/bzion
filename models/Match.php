<?php
/**
 * This file contains functionality relating to the official matches played in the league
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */
use BZIon\Model\Column\Timestamp;

/**
 * A match played between two teams
 * @package    BZiON\Models
 */
class Match extends UrlModel implements NamedModel
{
    const OFFICIAL = "official";
    const SPECIAL  = "special";
    const FUN      = "fm";

    const TEAM_V_TEAM   = 0;
    const TEAM_V_MIXED  = 1;
    const MIXED_V_MIXED = 2;

    use Timestamp;

    /**
     * The ID of the first team of the match
     * @var int
     */
    protected $team_a;

    /**
     * The ID of the second team of the match
     * @var int
     */
    protected $team_b;

    /**
     * The color of the first team
     * @var string
     */
    protected $team_a_color;

    /**
     * The color of the second team
     * @var string
     */
    protected $team_b_color;

    /**
     * The match points (usually the number of flag captures) Team A scored
     * @var int
     */
    protected $team_a_points;

    /**
     * The match points Team B scored
     * @var int
     */
    protected $team_b_points;

    /**
     * The BZIDs of players part of Team A who participated in the match, separated by commas
     * @var string
     */
    protected $team_a_players;

    /**
     * The BZIDs of players part of Team B who participated in the match, separated by commas
     * @var string
     */
    protected $team_b_players;

    /**
     * The ELO score of Team A after the match
     * @var int
     */
    protected $team_a_elo_new;

    /**
     * The ELO score of Team B after the match
     * @var int
     */
    protected $team_b_elo_new;

    /**
     * The map ID used in the match if the league supports more than one map
     * @var int
     */
    protected $map;

    /**
     * The type of match that occurred. Valid options: official, fm, special
     *
     * @var string
     */
    protected $match_type;

    /**
     * A JSON string of events that happened during a match, such as captures and substitutions
     * @var string
     */
    protected $match_details;

    /**
     * The server location of there the match took place
     * @var string
     */
    protected $server;

    /**
     * The file name of the replay file of the match
     * @var string
     */
    protected $replay_file;

    /**
     * The value of the ELO score difference
     * @var int
     */
    protected $elo_diff;

    /**
     * The value of the player Elo difference
     * @var int
     */
    protected $player_elo_diff;

    /**
     * The timestamp representing when the match information was last updated
     * @var TimeDate
     */
    protected $updated;

    /**
     * The duration of the match in minutes
     * @var int
     */
    protected $duration;

    /**
     * The ID of the person (i.e. referee) who last updated the match information
     * @var string
     */
    protected $entered_by;

    /**
     * The status of the match. Can be 'entered', 'disabled', 'deleted' or 'reported'
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "matches";

    const CREATE_PERMISSION = Permission::ENTER_MATCH;
    const EDIT_PERMISSION = Permission::EDIT_MATCH;
    const SOFT_DELETE_PERMISSION = Permission::SOFT_DELETE_MATCH;
    const HARD_DELETE_PERMISSION = Permission::HARD_DELETE_MATCH;

    /**
     * {@inheritdoc}
     */
    protected function assignResult($match)
    {
        $this->team_a = $match['team_a'];
        $this->team_b = $match['team_b'];
        $this->team_a_color = $match['team_a_color'];
        $this->team_b_color = $match['team_b_color'];
        $this->team_a_points = $match['team_a_points'];
        $this->team_b_points = $match['team_b_points'];
        $this->team_a_players = $match['team_a_players'];
        $this->team_b_players = $match['team_b_players'];
        $this->team_a_elo_new = $match['team_a_elo_new'];
        $this->team_b_elo_new = $match['team_b_elo_new'];
        $this->map = $match['map'];
        $this->match_type = $match['match_type'];
        $this->match_details = $match['match_details'];
        $this->server = $match['server'];
        $this->replay_file = $match['replay_file'];
        $this->elo_diff = $match['elo_diff'];
        $this->player_elo_diff = $match['player_elo_diff'];
        $this->timestamp = TimeDate::fromMysql($match['timestamp']);
        $this->updated = TimeDate::fromMysql($match['updated']);
        $this->duration = $match['duration'];
        $this->entered_by = $match['entered_by'];
        $this->status = $match['status'];
    }

    /**
     * Get the name of the route that shows the object
     * @param  string $action The route's suffix
     * @return string
     */
    public static function getRouteName($action = 'show')
    {
        return "match_$action";
    }

    /**
     * Get a one word description of a match relative to a team (i.e. win, loss, or draw)
     *
     * @param int|string|TeamInterface $teamID The team ID we want the noun for
     *
     * @return string Either "win", "loss", or "draw" relative to the team
     */
    public function getMatchDescription($teamID)
    {
        if ($this->getScore($teamID) > $this->getOpponentScore($teamID)) {
            return "win";
        } elseif ($this->getScore($teamID) < $this->getOpponentScore($teamID)) {
            return "loss";
        }

        return "tie";
    }

    /**
     * Get a one letter description of a match relative to a team (i.e. W, L, or T)
     *
     * @param int|string|TeamInterface $teamID The team ID we want the noun for
     *
     * @return string Either "W", "L", or "T" relative to the team
     */
    public function getMatchLetter($teamID)
    {
        return strtoupper(substr($this->getMatchDescription($teamID), 0, 1));
    }

    /**
     * Get the score of a specific team
     *
     * @param int|string|TeamInterface $teamID The team we want the score for
     *
     * @return int The score that team received
     */
    public function getScore($teamID)
    {
        if ($teamID instanceof TeamInterface) {
            // Oh no! The caller gave us a Team model instead of an ID!
            $teamID = $teamID->getId();
        } elseif (is_string($teamID)) {
            // Make sure we're comparing lowercase strings
            $teamID = strtolower($teamID);
        }

        if ($this->getTeamA()->getId() == $teamID) {
            return $this->getTeamAPoints();
        }

        return $this->getTeamBPoints();
    }

    /**
     * Get the score of the opponent relative to a team
     *
     * @param int|string|TeamInterface $teamID The opponent of the team we want the score for
     *
     * @return int The score of the opponent
     */
    public function getOpponentScore($teamID)
    {
        return $this->getScore($this->getOpponent($teamID));
    }

    /**
     * Get the opponent of a match relative to a team ID
     *
     * @param int|string|TeamInterface $teamID The team who is known in a match
     *
     * @return TeamInterface The opponent team
     */
    public function getOpponent($teamID)
    {
        if ($teamID instanceof TeamInterface) {
            $teamID = $teamID->getId();
        } elseif (is_string($teamID)) {
            $teamID = strtolower($teamID);
        }

        if ($this->getTeamA()->getId() == $teamID) {
            return $this->getTeamB();
        }

        return $this->getTeamA();
    }

    /**
     * Get the timestamp of the last update of the match
     *
     * @return TimeDate The match's update timestamp
     */
    public function getUpdated()
    {
        return $this->updated->copy();
    }

    /**
     * Set the timestamp of the match
     *
     * @param  mixed $timestamp The match's new timestamp
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->updateProperty($this->timestamp, "timestamp", TimeDate::from($timestamp));

        return $this;
    }

    /**
     * Get the first team involved in the match
     * @return TeamInterface Team A
     */
    public function getTeamA()
    {
        $team = Team::get($this->team_a);

        if ($this->match_type === self::OFFICIAL && $team->isValid()) {
            return $team;
        }

        return new ColorTeam($this->team_a_color);
    }

    /**
     * Get the second team involved in the match
     * @return TeamInterface Team B
     */
    public function getTeamB()
    {
        $team = Team::get($this->team_b);

        if ($this->match_type === self::OFFICIAL && $team->isValid()) {
            return $team;
        }

        return new ColorTeam($this->team_b_color);
    }

    /**
     * Get the color of Team A
     * @return string
     */
    public function getTeamAColor()
    {
        return $this->team_a_color;
    }

    /**
     * Get the color of Team B
     * @return string
     */
    public function getTeamBColor()
    {
        return $this->team_b_color;
    }

    /**
     * Get the list of players on Team A who participated in this match
     * @return Player[] Returns null if there were no players recorded for this match
     */
    public function getTeamAPlayers()
    {
        return $this->parsePlayers($this->team_a_players);
    }

    /**
     * Get the list of players on Team B who participated in this match
     * @return Player[] Returns null if there were no players recorded for this match
     */
    public function getTeamBPlayers()
    {
        return $this->parsePlayers($this->team_b_players);
    }

    /**
     * Get the list of players for a team in a match
     * @param  Team|int|null The team or team ID
     * @return Player[]|null Returns null if there were no players recorded for this match
     */
    public function getPlayers($team = null)
    {
        if ($team instanceof TeamInterface) {
            $team = $team->getId();
        }

        if ($this->getTeamA()->isValid() && $team == $this->getTeamA()->getId()) {
            return $this->getTeamAPlayers();
        } elseif ($this->getTeamB()->isValid() && $team == $this->getTeamB()->getId()) {
            return $this->getTeamBPlayers();
        }

        return $this->parsePlayers($this->team_a_players . "," . $this->team_b_players);
    }

    /**
     * Set the players of the match's teams
     *
     * @param int[] $teamAPlayers An array of player IDs
     * @param int[] $teamBPlayers An array of player IDs
     * @return self
     */
    public function setTeamPlayers($teamAPlayers = array(), $teamBPlayers = array())
    {
        $this->updateProperty($this->team_a_players, "team_a_players", implode(',', $teamAPlayers));
        $this->updateProperty($this->team_b_players, "team_b_players", implode(',', $teamBPlayers));

        return $this;
    }

    /**
     * Get an array of players based on a string representation
     * @param string $playerString
     * @return Player[] Returns null if there were no players recorded for this match
     */
    private function parsePlayers($playerString)
    {
        if ($playerString == null) {
            return [];
        }

        return Player::arrayIdToModel(explode(",", $playerString));
    }

    /**
     * Get the first team's points
     * @return int Team A's points
     */
    public function getTeamAPoints()
    {
        return $this->team_a_points;
    }

    /**
     * Get the second team's points
     * @return int Team B's points
     */
    public function getTeamBPoints()
    {
        return $this->team_b_points;
    }

    /**
     * Set the match team points
     *
     * @param  int $teamAPoints Team A's points
     * @param  int $teamBPoints Team B's points
     * @return self
     */
    public function setTeamPoints($teamAPoints, $teamBPoints)
    {
        $this->updateProperty($this->team_a_points, "team_a_points", $teamAPoints);
        $this->updateProperty($this->team_b_points, "team_b_points", $teamBPoints);

        return $this;
    }

    /**
     * Set the match team colors
     *
     * @param  ColorTeam|string $teamAColor The color of team A
     * @param  ColorTeam|string $teamBColor The color of team B
     * @return self
     */
    public function setTeamColors($teamAColor, $teamBColor)
    {
        if ($this->isOfficial()) {
            throw new \Exception("Cannot change team colors in an official match");
        }

        if ($teamAColor instanceof ColorTeam) {
            $teamAColor = $teamAColor->getId();
        }
        if ($teamBColor instanceof ColorTeam) {
            $teamBColor = $teamBColor->getId();
        }

        $this->updateProperty($this->team_a_color, "team_a_color", $teamAColor);
        $this->updateProperty($this->team_b_color, "team_b_color", $teamBColor);
    }

    /**
     * Get the ELO difference applied to each team's old ELO
     *
     * @param bool $absoluteValue Whether or not to get the absolute value of the Elo difference
     *
     * @return int The ELO difference
     */
    public function getEloDiff($absoluteValue = true)
    {
        return ($absoluteValue) ? abs($this->elo_diff) : $this->elo_diff;
    }

    /**
     * Get the Elo difference applied to players
     *
     * @param bool $absoluteValue Whether or not to get the absolute value of the Elo difference
     *
     * @return int The Elo difference for players
     */
    public function getPlayerEloDiff($absoluteValue = true)
    {
        return ($absoluteValue) ? abs($this->player_elo_diff) : $this->player_elo_diff;
    }

    /**
     * Get the first team's new ELO
     * @return int Team A's new ELO
     */
    public function getTeamAEloNew()
    {
        return $this->team_a_elo_new;
    }

    /**
     * Get the second team's new ELO
     * @return int Team B's new ELO
     */
    public function getTeamBEloNew()
    {
        return $this->team_b_elo_new;
    }

    /**
     * Get the first team's old ELO
     * @return int
     */
    public function getTeamAEloOld()
    {
        return $this->team_a_elo_new - $this->elo_diff;
    }

    /**
     * Get the second team's old ELO
     * @return int
     */
    public function getTeamBEloOld()
    {
        return $this->team_b_elo_new + $this->elo_diff;
    }

    /**
     * Get the team's new ELO
     * @param  Team $team The team whose new ELO to return
     * @return int|null   The new ELO, or null if the team provided has not
     *                    participated in the match
     */
    public function getTeamEloNew(Team $team)
    {
        if ($team->getId() == $this->team_a) {
            return $this->getTeamAEloNew();
        } elseif ($team->getId() == $this->team_b) {
            return $this->getTeamBEloNew();
        }

        return null;
    }

    /**
     * Get the team's old ELO
     * @param  Team $team The team whose old ELO to return
     * @return int|null   The old ELO, or null if the team provided has not
     *                    participated in the match
     */
    public function getTeamEloOld(Team $team)
    {
        if ($team->getId() == $this->team_a) {
            return $this->getTeamAEloOld();
        } elseif ($team->getId() == $this->team_b) {
            return $this->getTeamBEloOld();
        }

        return null;
    }

    /**
     * Get the map where the match was played on
     * @return Map Returns an invalid map if no map was found
     */
    public function getMap()
    {
        return Map::get($this->map);
    }

    /**
     * Set the map where the match was played
     * @param  int $map The ID of the map
     * @return self
     */
    public function setMap($map)
    {
        $this->updateProperty($this->map, "map", $map, "s");
    }

    /**
     * Get the type of official match this is. Whether it has just traditional teams or has mixed teams.
     *
     * Possible official match types:
     *   - Team vs Team
     *   - Team vs Mixed
     *   - Mixed vs Mixed
     *
     * @see Match::TEAM_V_TEAM
     * @see Match::TEAM_V_MIXED
     * @see Match::MIXED_V_MIXED
     *
     * @return int
     */
    public function getTeamMatchType()
    {
        if ($this->getTeamA()->supportsMatchCount() && $this->getTeamB()->supportsMatchCount()) {
            return self::TEAM_V_TEAM;
        } elseif ($this->getTeamA()->supportsMatchCount() xor $this->getTeamB()->supportsMatchCount()) {
            return self::TEAM_V_MIXED;
        }

        return self::MIXED_V_MIXED;
    }

    /**
     * Get the match type
     *
     * @return string 'official', 'fm', or 'special'
     */
    public function getMatchType()
    {
        return $this->match_type;
    }

    /**
     * Set the match type
     *
     * @param  string $matchType A valid match type; official, fm, special
     *
     * @return static
     */
    public function setMatchType($matchType)
    {
        return $this->updateProperty($this->match_type, "match_type", $matchType, 's');
    }

    /**
     * Get a JSON decoded array of events that occurred during the match
     * @return mixed|null Returns null if there were no events recorded for the match
     */
    public function getMatchDetails()
    {
        return json_decode($this->match_details);
    }

    /**
     * Get the server address of the server where this match took place
     * @return string|null Returns null if there was no server address recorded
     */
    public function getServerAddress()
    {
        return $this->server;
    }

    /**
     * Set the server address of the server where this match took place
     *
     * @param  string|null $server The server hostname
     * @param  int|null    $port   The server port
     * @return self
     */
    public function setServerAddress($server = null)
    {
        $this->updateProperty($this->server, "server", $server);

        return $this;
    }

    /**
     * Get the name of the replay file for this specific map
     * @param  int    $length The length of the replay file name; it will be truncated
     * @return string Returns null if there was no replay file name recorded
     */
    public function getReplayFileName($length = 0)
    {
        if ($length > 0) {
            return substr($this->replay_file, 0, $length);
        }

        return $this->replay_file;
    }

    /**
     * Get the match duration
     * @return int The duration of the match in minutes
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set the match duration
     *
     * @param  int  $duration The new duration of the match in minutes
     * @return self
     */
    public function setDuration($duration)
    {
        return $this->updateProperty($this->duration, "duration", $duration);
    }

    /**
     * Get the user who entered the match
     * @return Player
     */
    public function getEnteredBy()
    {
        return Player::get($this->entered_by);
    }

    /**
     * Get the loser of the match
     *
     * @return TeamInterface The team that was the loser or the team with the lower elo if the match was a draw
     */
    public function getLoser()
    {
        // Get the winner of the match
        $winner = $this->getWinner();

        // Get the team that wasn't the winner... Duh
        return $this->getOpponent($winner);
    }

    /**
     * Get the winner of a match
     *
     * @return TeamInterface The team that was the victor or the team with the lower elo if the match was a draw
     */
    public function getWinner()
    {
        // "As Mother Teresa once said, it's not enough if you win. Others must lose."
        //   -Stephen Colbert

        // Get the team that had its Elo increased or the team whose players had their Elo increased
        if ($this->elo_diff > 0 || $this->player_elo_diff > 0) {
            return $this->getTeamA();
        } elseif ($this->elo_diff < 0 || $this->player_elo_diff < 0) {
            return $this->getTeamB();
        } elseif ($this->team_a_points > $this->team_b_points) {
            // In case we're dealing with a match such an FM that doesn't have an ELO difference
            return $this->getTeamA();
        } elseif ($this->team_a_points < $this->team_b_points) {
            return $this->getTeamB();
        }

        // If the scores are the same, return Team A because well, fuck you that's why
        return $this->getTeamA();
    }

    /**
     * Determine whether the match was a draw
     * @return bool True if the match ended without any winning teams
     */
    public function isDraw()
    {
        return $this->team_a_points == $this->team_b_points;
    }

    /**
     * Find out whether the match involves a team
     *
     * @param  TeamInterface $team The team to check
     * @return bool
     */
    public function involvesTeam($team)
    {
        return $team->getId() == $this->getTeamA()->getId() || $team->getId() == $this->getTeamB()->getId();
    }

    /**
     * Find out if the match is played between official teams
     */
    public function isOfficial()
    {
        return self::OFFICIAL === $this->getMatchType();
    }

    /**
     * Reset the ELOs of the teams participating in the match
     *
     * @return self
     */
    public function resetELOs()
    {
        if ($this->match_type === self::OFFICIAL) {
            $this->getTeamA()->supportsMatchCount() && $this->getTeamA()->changeELO(-$this->elo_diff);
            $this->getTeamB()->supportsMatchCount() && $this->getTeamB()->changeELO(+$this->elo_diff);
        }

        return $this;
    }

    /**
     * Calculate the match's contribution to the team activity
     *
     * @return float
     */
    public function getActivity()
    {
        $daysPassed = $this->getTimestamp()->diffInSeconds();
        $daysPassed = $daysPassed / TimeDate::SECONDS_PER_MINUTE / TimeDate::MINUTES_PER_HOUR / TimeDate::HOURS_PER_DAY;

        $activity = 0.0116687059537612 * (pow(45 - $daysPassed, (1 / 6)) + atan(31.0 - $daysPassed) / 2.0);

        if (is_nan($activity) || $activity < 0.0) {
            return 0.0;
        }

        return $activity;
    }

    /**
     * Enter a new match to the database
     * @param  int             $a          Team A's ID
     * @param  int             $b          Team B's ID
     * @param  int             $a_points   Team A's match points
     * @param  int             $b_points   Team B's match points
     * @param  int             $duration   The match duration in minutes
     * @param  int|null        $entered_by The ID of the player reporting the match
     * @param  string|DateTime $timestamp  When the match was played
     * @param  int[]           $a_players  The IDs of the first team's players
     * @param  int[]           $b_players  The IDs of the second team's players
     * @param  string|null     $server     The address of the server where the match was played
     * @param  int|null        $port       The port of the server where the match was played
     * @param  string          $replayFile The name of the replay file of the match
     * @param  int             $map        The ID of the map where the match was played, only for rotational leagues
     * @param  string          $matchType  The type of match (e.g. official, fm, special)
     * @param  string          $a_color    Team A's color
     * @param  string          $b_color    Team b's color
     * @return Match           An object representing the match that was just entered
     */
    public static function enterMatch(
        $a, $b, $a_points, $b_points, $duration, $entered_by, $timestamp = "now",
        $a_players = array(), $b_players = array(), $server = null, $replayFile = null,
        $map = null, $matchType = "official", $a_color = null, $b_color = null
    ) {
        $matchData = array(
            'team_a_color'   => strtolower($a_color),
            'team_b_color'   => strtolower($b_color),
            'team_a_points'  => $a_points,
            'team_b_points'  => $b_points,
            'team_a_players' => implode(',', $a_players),
            'team_b_players' => implode(',', $b_players),
            'timestamp'      => TimeDate::from($timestamp)->toMysql(),
            'duration'       => $duration,
            'entered_by'     => $entered_by,
            'server'         => $server,
            'replay_file'    => $replayFile,
            'map'            => $map,
            'status'         => 'entered',
            'match_type'     => $matchType
        );

        $playerEloDiff = null;

        if ($matchType === self::OFFICIAL) {
            $team_a = Team::get($a);
            $team_b = Team::get($b);

            $a_players_elo = null;
            $b_players_elo = null;

            // Only bother if we have players reported for both teams
            if (!empty($a_players) && !empty($b_players)) {
                $a_players_elo = self::getAveragePlayerElo($a_players);
                $b_players_elo = self::getAveragePlayerElo($b_players);

                $playerEloDiff = self::calculateEloDiff($a_players_elo, $b_players_elo, $a_points, $b_points, $duration);
            }

            // If it's a Team vs Team official match, we need to calculate the Elo diff between the two teams. Otherwise,
            // we'll be using the player Elo diff as the "team" Elo diff for future calculations and database persistence
            $teamEloDiff = $playerEloDiff;

            if ($team_a->isValid() && $team_b->isValid()) {
                $teamEloDiff = self::calculateEloDiff($team_a->getElo(), $team_b->getElo(), $a_points, $b_points, $duration);
            }

            $matchData['elo_diff'] = $teamEloDiff;
            $matchData['player_elo_diff'] = $playerEloDiff;

            // Update team ELOs
            if ($team_a->isValid()) {
                $team_a->adjustElo($teamEloDiff);

                $matchData['team_a'] = $a;
                $matchData['team_a_elo_new'] = $team_a->getElo();
            }
            if ($team_b->isValid()) {
                $team_b->adjustElo(-$teamEloDiff);

                $matchData['team_b'] = $b;
                $matchData['team_b_elo_new'] = $team_b->getElo();
            }
        }

        $match = self::create($matchData, 'updated');
        $match->updateMatchCount();

        $players = $match->getPlayers();

        $db = Database::getInstance();
        $db->startTransaction();

        /** @var Player $player */
        foreach ($players as $player) {
            $diff = $playerEloDiff;

            if ($playerEloDiff !== null && !in_array($player->getId(), $a_players)) {
                $diff = -$playerEloDiff;
            }

            $player->setMatchParticipation($match, $diff);
        }

        $db->finishTransaction();

        return $match;
    }

    /**
     * Calculate the ELO score difference
     *
     * Computes the ELO score difference on each team after a match, based on
     * GU League's rules.
     *
     * @param  int $a_elo    Team A's current ELO score
     * @param  int $b_elo    Team B's current ELO score
     * @param  int $a_points Team A's match points
     * @param  int $b_points Team B's match points
     * @param  int $duration The match duration in minutes
     * @return int The ELO score difference
     */
    public static function calculateEloDiff($a_elo, $b_elo, $a_points, $b_points, $duration)
    {
        $prob = 1.0 / (1 + pow(10, (($b_elo - $a_elo) / 400.0)));
        if ($a_points > $b_points) {
            $diff = 50 * (1 - $prob);
        } elseif ($a_points == $b_points) {
            $diff = 50 * (0.5 - $prob);
        } else {
            $diff = 50 * (0 - $prob);
        }

        // Apply ELO modifiers from `config.yml`
        $durations = Service::getParameter('bzion.league.duration');
        $diff *= (isset($durations[$duration])) ? $durations[$duration] : 1;

        if (abs($diff) < 1 && $diff != 0) {
            // ELOs such as 0.75 should round up to 1...
            return ($diff > 0) ? 1 : -1;
        }

        // ...everything else is rounded down (-3.7 becomes -3 and 48.1 becomes 48)
        return intval($diff);
    }

    /**
     * Find if a match's stored ELO is correct
     */
    public function isEloCorrect()
    {
        return $this->elo_diff === $this->calculateEloDiff(
            $this->getTeamAEloOld(),
            $this->getTeamBEloOld(),
            $this->getTeamAPoints(),
            $this->getTeamBPoints(),
            $this->getDuration()
        );
    }

    /**
     * Recalculate the match's elo and adjust the team ELO values
     */
    public function recalculateElo()
    {
        if ($this->match_type !== self::OFFICIAL) {
            return;
        }

        $a = $this->getTeamA();
        $b = $this->getTeamB();

        $elo = $this->calculateEloDiff(
            $a->getElo(),
            $b->getElo(),
            $this->getTeamAPoints(),
            $this->getTeamBPoints(),
            $this->getDuration()
        );

        $this->updateProperty($this->elo_diff, "elo_diff", $elo);

        $a->changeElo($elo);
        $b->changeElo(-$elo);

        $this->updateProperty($this->team_a_elo_new, "team_a_elo_new", $a->getElo());
        $this->updateProperty($this->team_b_elo_new, "team_b_elo_new", $b->getElo());
    }

    /**
     * Get all the matches in the database
     */
    public static function getMatches()
    {
        return self::getQueryBuilder()->active()->getModels();
    }

    /**
     * Get a query builder for matches
     * @return MatchQueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new MatchQueryBuilder('Match', array(
            'columns' => array(
                'firstTeam'        => 'team_a',
                'secondTeam'       => 'team_b',
                'firstTeamPoints'  => 'team_a_points',
                'secondTeamPoints' => 'team_b_points',
                'time'             => 'timestamp',
                'map'              => 'map',
                'type'             => 'match_type',
                'status'           => 'status'
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->updateMatchCount(true);

        parent::delete();
    }

    /**
     * {@inheritdoc}
     */
    public static function getActiveStatuses()
    {
        return array('entered');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $description = '';

        switch ($this->getMatchType()) {
            case self::OFFICIAL:
                // Only show Elo diff if both teams are actual teams
                if ($this->getTeamA()->supportsMatchCount() && $this->getTeamB()->supportsMatchCount()) {
                    $description = "(+/- {$this->getEloDiff()})";
                }
                break;

            case self::FUN:
                $description = 'Fun Match:';
                break;

            case self::SPECIAL:
                $description = 'Special Match:';
                break;

            default:
                break;
        }

        return trim(sprintf('%s %s [%d] vs [%d] %s',
            $description,
            $this->getWinner()->getName(),
            $this->getScore($this->getWinner()),
            $this->getScore($this->getLoser()),
            $this->getLoser()->getName()
        ));
    }

    /**
     * Get the average ELO for an array of players
     *
     * @param int[] $players
     *
     * @return float|int
     */
    private static function getAveragePlayerElo($players)
    {
        $getElo = function ($n) {
            return Player::get($n)->getElo();
        };

        return array_sum(array_map($getElo, $players)) / count($players);
    }

    /**
     * Update the match count of the teams participating in the match
     *
     * @param bool $decrement Whether to decrement instead of incrementing the match count
     */
    private function updateMatchCount($decrement = false)
    {
        if ($this->match_type !== self::OFFICIAL) {
            return;
        }

        $diff = ($decrement) ? -1 : 1;

        if ($this->isDraw()) {
            $this->getTeamA()->supportsMatchCount() && $this->getTeamA()->changeMatchCount($diff, 'draw');
            $this->getTeamB()->supportsMatchCount() && $this->getTeamB()->changeMatchCount($diff, 'draw');
        } else {
            $this->getWinner()->supportsMatchCount() && $this->getWinner()->changeMatchCount($diff, 'win');
            $this->getLoser()->supportsMatchCount()  && $this->getLoser()->changeMatchCount($diff, 'loss');
        }
    }
}
