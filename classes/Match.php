<?php

/**
 * A match played between two teams
 */
class Match extends Model
{

    /**
     * The ID of the first team of the match
     * @var int
     */
    private $team_a;

    /**
     * The ID of the second team of the match
     * @var int
     */
    private $team_b;

    /**
     * The match points (usually the number of flag captures) Team A scored
     * @var int
     */
    private $team_a_points;

     /**
     * The match points Team B scored
     * @var int
     */
    private $team_b_points;

    /**
     * The BZIDs of players part of Team A who participated in the match, separated by commas
     * @var string
     */
    private $team_a_players;

    /**
     * The BZIDs of players part of Team B who participated in the match, separated by commas
     * @var string
     */
    private $team_b_players;

     /**
     * The ELO score of Team A after the match
     * @var int
     */
    private $team_a_elo_new;

     /**
     * The ELO score of Team B after the match
     * @var int
     */
    private $team_b_elo_new;

    /**
     * The map name used in the match if the league supports more than one map
     * @var string
     */
    private $map_played;

    /**
     * A JSON string of events that happened during a match, such as captures and substitutions
     * @var string
     */
    private $match_details;

    /**
     * The port of the server where the match took place
     * @var int
     */
    private $port;

    /**
     * The server location of there the match took place
     * @var string
     */
    private $server;

    /**
     * The file name of the replay file of the match
     * @var string
     */
    private $replay_file;

    /**
     * The absolute value of the ELO score difference
     * @var int
     */
    private $elo_diff;

    /**
     * The timestamp representing when the match was played
     * @var string
     */
    private $timestamp;

    /**
     * The timestamp representing when the match information was last updated
     * @var string
     */
    private $updated;

    /**
     * The duration of the match in minutes
     * @var int
     */
    private $duration;

    /**
     * The ID of the person (i.e. referee) who last updated the match information
     * @var string
     */
    private $entered_by;

    /**
     * The status of the match. Can be 'entered', 'disabled', 'deleted' or 'reported'
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "matches";

    /**
     * Construct a new Match
     * @param int $id The match's ID
     */
    function __construct($id) {

        parent::__construct($id);
        if (!$this->valid) return;

        $match = $this->result;

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
     * Get the timestamp of the match
     * @return string The match's timestamp
     */
    function getTimestamp() {
        return $this->timestamp->diffForHumans();
    }

    /**
     * Get the first team involved in the match
     * @return Team Team A's id
     */
    function getTeamA() {
        return new Team($this->team_a);
    }

    /**
     * Get the second team involved in the match
     * @return Team Team B's id
     */
    function getTeamB() {
        return new Team($this->team_b);
    }

    /**
     * Get the list of players on Team A who participated in this match
     * @return Player[]|null Returns null if there were no players recorded for this match
     */
    function getTeamAPlayers() {
        $team_A_Players = array();

        if ($this->team_a_players == null)
        {
            return null;
        }

        $BZIDs = explode(",", $this->team_a_players);

        foreach ($BZIDs as $bzid)
        {
            $team_A_Players[] = Player::getFromBZID($bzid);
        }

        return $team_A_Players;
    }

    /**
     * Get the list of players on Team B who participated in this match
     * @return Player[]|null Returns null if there were no players recorded for this match
     */
    function getTeamBPlayers() {
        $team_B_Players = array();

        if ($this->team_b_players == null)
        {
            return null;
        }

        $BZIDs = explode(",", $this->team_b_players);

        foreach ($BZIDs as $bzid)
        {
            $team_B_Players[] = Player::getFromBZID($bzid);
        }

        return $team_B_Players;
    }

    /**
     * Get the first team's points
     * @return int Team A's points
     */
    function getTeamAPoints() {
        return $this->team_a_points;
    }

    /**
     * Get the second team's points
     * @return int Team B's points
     */
    function getTeamBPoints() {
        return $this->team_b_points;
    }

    /**
     * Get the ELO difference applied to each team's old ELO
     * @return int The ELO difference
     */
    function getEloDiff() {
        return $this->elo_diff;
    }

    /**
     * Get the first team's new ELO
     * @return int Team A's new ELO
     */
    function getTeamAEloNew() {
        return $this->team_a_elo_new;
    }

    /**
     * Get the second team's new ELO
     * @return int Team B's new ELO
     */
    function getTeamBEloNew() {
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
    public function getMatchDetails() {
        return json_decode($this->match_details);
    }

    /**
     * Get the server address of the server where this match took place
     * @return string|null Returns null if there was no server address recorded
     */
    public function getServerAddress()
    {
        if ($this->port == null || $this->server == null)
        {
            return null;
        }

        return $this->server . ":" . $this->port;
    }

    /**
     * Get the name of the replay file for this specific map
     * @param int $length The length of the replay file name; it will be truncated
     * @return string|null Returns null if there was no replay file name recorded
     */
    public function getReplayFileName($length = 0)
    {
        if ($length > 0)
        {
            return substr($this->replay_file, 0, $length);
        }

        return $this->replay_file;
    }

    /**
     * Get the match duration
     * @return int The duration
     */
    function getDuration() {
        return $this->duration;
    }

    /**
     * Get the user who entered the match
     * @return Player
     */
    function getEnteredBy() {
        return new Player($this->entered_by);
    }

    /**
     * Determine whether the match was a draw
     * @return bool True if the match ended without any winning teams
     */
    function isDraw() {
        return $this->team_a_points == $this->team_b_points;
    }

    /**
     * Enter a new match to the database
     * @param int $a Team A's ID
     * @param int $b Team B's ID
     * @param int $a_points Team A's match points
     * @param int $b_points Team B's match points
     * @param int $duration The match duration in minutes
     * @param $entered_by
     * @param string $timestamp When the match was played
     * @return Match An object representing the match that was just entered
     */
    public static function enterMatch($a, $b, $a_points, $b_points, $duration, $entered_by, $timestamp = "now") {
        $db = Database::getInstance();

        $team_a = new Team($a);
        $team_b = new Team($b);
        $a_elo = $team_a->getElo();
        $b_elo = $team_b->getElo();

        $diff = Match::calculateEloDiff($a_elo, $b_elo, $a_points, $b_points, $duration);

        $a_elo += $diff;
        $b_elo -= $diff;

        // Update team ELOs
        $team_a->changeElo($diff);
        $team_b->changeElo(-$diff);

        $diff = abs($diff);

        $timestamp = new TimeDate($timestamp);

        $db->query("INSERT INTO matches (team_a, team_b, team_a_points, team_b_points, team_a_elo_new, team_b_elo_new, elo_diff, timestamp, updated, duration, entered_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
        "iiiiiiisiis", array($a, $b, $a_points, $b_points, $a_elo, $b_elo, $diff, $timestamp->format(DATE_FORMAT), $duration, $entered_by, "entered"));

        $id = $db->getInsertId();

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

        return new Match($id);
    }

    /**
     * Calculate the ELO score difference
     *
     * Computes the absolute value of the ELO score difference on each team
     * after a match, based on GU League's rules.
     *
     * @param int $a_elo Team A's current ELO score
     * @param int $b_elo Team B's current ELO score
     * @param int $a_points Team A's match points
     * @param int $b_points Team B's match points
     * @param int $duration The match duration in minutes
     * @return int The ELO score difference
     */
    public static function calculateEloDiff($a_elo, $b_elo, $a_points, $b_points, $duration) {
        $prob = 1.0 / (1 + pow(10, (($b_elo-$a_elo)/400.0)));
        if ($a_points > $b_points) {
           $diff = 50*(1-$prob);
        } else if ($a_points == $b_points) {
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
     * @param int $start The offset used when fetching matches, i.e. the starting point
     * @param int $limit The amount of matches to be retrieved
     * @return Match[] An array of match IDs
     */
    public static function getMatches($start = 0, $limit = 50) {
        $matches = array();
        $matchIDs = parent::fetchIdsFrom("status", array(
                    "disabled",
                    "deleted"
                   ), "s", true, "ORDER BY timestamp DESC LIMIT $limit OFFSET $start");

        foreach ($matchIDs as $matchID)
        {
            $matches[] = new Match($matchID);
        }

        return $matches;
    }

    /**
     * Get the matches that a team took part of
     * @param int $teamID The team ID of whose matches to search for
     * @return Match[] An array of matches where the team participated in
     */
    public static function getMatchesByTeam($teamID)
    {
        $matches = array();
        $matchIDs = parent::fetchIds("WHERE team_a=? OR team_b=?", "ii", array($teamID, $teamID));

        foreach ($matchIDs as $matchID)
        {
            $matches[] = new Match($matchID);
        }

        return $matches;
    }
}
