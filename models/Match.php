<?php
/**
 * This file contains functionality relating to the official matches played in the league
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A match played between two teams
 * @package    BZiON\Models
 */
class Match extends Model implements PermissionModel
{

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
     * The map name used in the match if the league supports more than one map
     * @var string
     */
    protected $map_played;

    /**
     * A JSON string of events that happened during a match, such as captures and substitutions
     * @var string
     */
    protected $match_details;

    /**
     * The port of the server where the match took place
     * @var int
     */
    protected $port;

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
     * The absolute value of the ELO score difference
     * @var int
     */
    protected $elo_diff;

    /**
     * The timestamp representing when the match was played
     * @var string
     */
    protected $timestamp;

    /**
     * The timestamp representing when the match information was last updated
     * @var string
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

    /**
     * {@inheritDoc}
     */
    protected function assignResult($match)
    {
        $this->team_a = $match['team_a'];
        $this->team_b = $match['team_b'];
        $this->team_a_points = $match['team_a_points'];
        $this->team_b_points = $match['team_b_points'];
        $this->team_a_players = $match['team_a_players'];
        $this->team_b_players = $match['team_b_players'];
        $this->team_a_elo_new = $match['team_a_elo_new'];
        $this->team_b_elo_new = $match['team_b_elo_new'];
        $this->map_played = $match['map_played'];
        $this->match_details = $match['match_details'];
        $this->port = $match['port'];
        $this->server = $match['server'];
        $this->replay_file = $match['replay_file'];
        $this->elo_diff = $match['elo_diff'];
        $this->timestamp = new TimeDate($match['timestamp']);
        $this->updated = new TimeDate($match['updated']);
        $this->duration = $match['duration'];
        $this->entered_by = $match['entered_by'];
        $this->status = $match['status'];
    }

    /**
     * Get a one word description of a match relative to a team (i.e. win, loss, or draw)
     *
     * @param int $teamID The team ID we want the noun for
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

        return "draw";
    }

    /**
     * Get the score of a specific team
     *
     * @param int $teamID The team we want the score for
     *
     * @return int The score that team received
     */
    public function getScore($teamID)
    {
        if ($this->getTeamA()->getId() == $teamID) {
            return $this->getTeamAPoints();
        }

        return $this->getTeamBPoints();
    }

    /**
     * Get the score of the opponent relative to a team
     *
     * @param int $teamID The opponent of the team we want the score for
     *
     * @return int The score of the opponent
     */
    public function getOpponentScore($teamID)
    {
        if ($this->getTeamA()->getId() != $teamID) {
            return $this->getTeamAPoints();
        }

        return $this->getTeamBPoints();
    }

    /**
     * Get the opponent of a match relative to a team ID
     *
     * @param int $teamID The team who is known in a match
     *
     * @return Team The opponent team
     */
    public function getOpponent($teamID)
    {
        if ($this->getTeamA()->getId() == $teamID) {
            return $this->getTeamB();
        }

        return $this->getTeamA();
    }

    /**
     * Get the timestamp of the match
     * @return string The match's timestamp
     */
    public function getTimestamp()
    {
        return $this->timestamp->diffForHumans();
    }

    /**
     * Get the first team involved in the match
     * @return Team Team A's id
     */
    public function getTeamA()
    {
        return new Team($this->team_a);
    }

    /**
     * Get the second team involved in the match
     * @return Team Team B's id
     */
    public function getTeamB()
    {
        return new Team($this->team_b);
    }

    /**
     * Get the list of players on Team A who participated in this match
     * @return Player[]|null Returns null if there were no players recorded for this match
     */
    public function getTeamAPlayers()
    {
        return $this->parsePlayers($this->team_b_players);
    }

    /**
     * Get the list of players on Team B who participated in this match
     * @return Player[]|null Returns null if there were no players recorded for this match
     */
    public function getTeamBPlayers()
    {
        return $this->parsePlayers($this->team_a_players);
    }

    /**
     * Get an array of players based on a string representation
     * @return Player[]|null Returns null if there were no players recorded for this match
     */
    private static function parsePlayers($playerString)
    {
        $players = array();

        if ($playerString == null) {
            return null;
        }

        $BZIDs = explode(",", $playerString);

        foreach ($BZIDs as $bzid) {
            $players[] = Player::getFromBZID($bzid);
        }

        return $players;
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
     * Get the ELO difference applied to each team's old ELO
     * @return int The ELO difference
     */
    public function getEloDiff()
    {
        return $this->elo_diff;
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
     * Get the name of the map where the match was played on
     * @return string|null Returns null if the league doesn't host multiple maps
     */
    public function getMapPlayed()
    {
        return $this->map_played;
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
        if ($this->port == null || $this->server == null) {
            return null;
        }

        return $this->server . ":" . $this->port;
    }

    /**
     * Get the name of the replay file for this specific map
     * @param  int         $length The length of the replay file name; it will be truncated
     * @return string|null Returns null if there was no replay file name recorded
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
     * @return int The duration
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Get the user who entered the match
     * @return Player
     */
    public function getEnteredBy()
    {
        return new Player($this->entered_by);
    }

    /**
     * Get the loser of the match
     *
     * @return Team The team that was the loser or the team with the lower elo if the match was a draw
     */
    public function getLoser()
    {
        // Get the winner of the match
        $winner = $this->getWinner();

        // Get the team that wasn't the winner... Duh
        return $this->getOpponent($winner->getId());
    }

    /**
     * Get the winner of a match
     *
     * @return Team The team that was the victor or the team with the lower elo if the match was a draw
     */
    public function getWinner()
    {
        // Get the winner if the match had one
        if ($this->getTeamAPoints() > $this->getTeamBPoints()) {
            return $this->getTeamA();
        } elseif ($this->getTeamBPoints() > $this->getTeamAPoints()) {
            return $this->getTeamB();
        }

        // It was a draw, so grab the team with the lower elo because they're the underdogs
        if ($this->getTeamA()->getElo() > $this->getTeamB()->getElo()) {
            return $this->getTeamB();
        } elseif ($this->getTeamB()->getElo() > $this->getTeamA()->getElo()) {
            return $this->getTeamA();
        }

        // If the ELOs are the same, return Team A because well, fuck you that's why
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
     * Enter a new match to the database
     * @param  int             $a          Team A's ID
     * @param  int             $b          Team B's ID
     * @param  int             $a_points   Team A's match points
     * @param  int             $b_points   Team B's match points
     * @param  int             $duration   The match duration in minutes
     * @param  int             $entered_by The ID of the player reporting the match
     * @param  string|DateTime $timestamp  When the match was played
     * @return Match           An object representing the match that was just entered
     */
    public static function enterMatch($a, $b, $a_points, $b_points, $duration, $entered_by, $timestamp = "now")
    {
        $team_a = new Team($a);
        $team_b = new Team($b);
        $a_elo = $team_a->getElo();
        $b_elo = $team_b->getElo();

        $diff = self::calculateEloDiff($a_elo, $b_elo, $a_points, $b_points, $duration);

        // Update team ELOs
        $team_a->changeElo($diff);
        $team_b->changeElo(-$diff);

        $timestamp = TimeDate::from($timestamp);

        $match = self::create(array(
            'team_a' => $a,
            'team_b' => $b,
            'team_a_points' => $a_points,
            'team_b_points' => $b_points,
            'team_a_elo_new' => $team_a->getElo(),
            'team_b_elo_new' => $team_b->getElo(),
            'elo_diff' => abs($diff),
            'timestamp' => $timestamp->toMysql(),
            'duration' => $duration,
            'entered_by' => $entered_by,
            'status' => 'entered'
        ), 'iiiiiiisiis', 'updated');

        // Update team match count
        if ($a_points == $b_points) {
            $team_a->incrementMatchCount("draw");
            $team_b->incrementMatchCount("draw");
        } elseif ($a_points > $b_points) {
            $team_a->incrementMatchCount("win");
            $team_b->incrementMatchCount("loss");
        } else {
            $team_a->incrementMatchCount("loss");
            $team_b->incrementMatchCount("win");
        }

        return $match;
    }

    /**
     * Calculate the ELO score difference
     *
     * Computes the absolute value of the ELO score difference on each team
     * after a match, based on GU League's rules.
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
        $prob = 1.0 / (1 + pow(10, (($b_elo-$a_elo)/400.0)));
        if ($a_points > $b_points) {
           $diff = 50*(1-$prob);
        } elseif ($a_points == $b_points) {
            $diff = 50*(0.5-$prob);
        } else {
            $diff = 50*(0-$prob);
        }

        $durations = unserialize(DURATION);

        foreach ($durations as $time => $modifier) {
            if ($duration == $time) {
                return floor($modifier*$diff);
            }
        }

        return floor($diff);
    }

    /**
     * Get all the matches in the database that aren't disabled or deleted
     * @param  int     $start The offset used when fetching matches, i.e. the starting point
     * @param  int     $limit The amount of matches to be retrieved
     * @return Match[] An array of match IDs
     */
    public static function getMatches($start = 0, $limit = 50)
    {
        return self::arrayIdToModel(
            parent::fetchIdsFrom(
                "status", array("disabled", "deleted"), "s", true,
                "ORDER BY timestamp DESC LIMIT $limit OFFSET $start"
            )
        );
    }

    /**
     * Get the matches that a team took part of
     *
     * @param int    $teamID    The team ID of whose matches to search for
     * @param string $matchType The filter for match types: "all", "wins", "losses", or "draws"
     * @param int    $start     The offset used when fetching matches, i.e. the starting point
     * @param int    $limit     The amount of matches to be retrieved
     *
     * @return Match[] An array of matches where the team participated in
     */
    public static function getMatchesByTeam($teamID, $matchType = "all", $start = 0, $limit = 5)
    {
        $query = "WHERE ";

        if ($matchType == "wins") {
            $query .= "(team_a = ? AND team_a_points > team_b_points) OR (team_b = ? AND team_b_points > team_a_points)";
        } elseif ($matchType == "losses") {
            $query .= "(team_a = ? AND team_b_points > team_a_points) OR (team_b = ? AND team_a_points > team_b_points)";
        } elseif ($matchType == "draws") {
            $query .= "((team_a = ? OR team_b = ?) AND team_a_points = team_b_points)";
        } else {
            $query .= "team_a = ? OR team_b = ?";
        }

        $query .= " ORDER BY timestamp DESC LIMIT $limit OFFSET $start";

        return self::arrayIdToModel(
            parent::fetchIds($query, "ii", array($teamID, $teamID))
        );
    }

    public static function getCreatePermission() { return Permission::ENTER_MATCH; }
    public static function getEditPermission() { return Permission::EDIT_MATCH;  }
    public static function getSoftDeletePermission() { return Permission::SOFT_DELETE_MATCH; }
    public static function getHardDeletePermission() { return Permission::HARD_DELETE_MATCH; }
}
