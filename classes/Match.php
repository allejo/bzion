<?php

class Match extends Controller
{

    /**
     * The ID of the first team of the match
     * @todo Does Team A represent the winner? Or is the team assignment random?
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
     * The BZID of the person (i.e. referee) who last updated the match information
     * @var string
     */
    private $entered_by;

    /**
     * The status of the match. Can be 'entered', 'disabled', 'deleted' or 'reported'
     * @var string
     */
    private $status;

    /**
     * Construct a new Match
     * @param int $id The match's ID
     */
    function __construct($id) {

        parent::__construct($id, "matches");
        $match = $this->result;

        $this->team_a = $match['team_a'];
        $this->team_b = $match['team_b'];
        $this->team_a_points = $match['team_a_points'];
        $this->team_b_points = $match['team_b_points'];
        $this->team_a_elo_new = $match['team_a_elo_new'];
        $this->team_b_elo_new = $match['team_b_elo_new'];
        $this->elo_diff = $match['elo_diff'];
        $this->timestamp = new DateTime($match['timestamp']);
        $this->updated = new DateTime($match['updated']);
        $this->duration = $match['duration'];
        $this->entered_by = $match['entered_by'];
        $this->status = $match['status'];

    }

    /**
     * Enter a new match to the database
     * @param int $a Team A's ID
     * @param int $b Team B's ID
     * @param int $a_points Team A's match points
     * @param int $b_points Team B's match points
     * @param int $duration The match duration in minutes
     * @param string $timestamp When the match was played
     * @return Match An object representing the match that was just entered
     */
    public static function enterMatch($a, $b, $a_points, $b_points, $duration, $entered_by, $timestamp = "now") {
        $db = Database::getInstance();

        $team_a = new Team($a);
        $team_b = new Team($b);
        $a_elo = $team_a->elo;
        $b_elo = $team_b->elo;

        $diff = Match::calculateEloDiff($a_elo, $b_elo, $a_points, $b_points, $duration);

        $a_elo += $diff;
        $b_elo -= $diff;
        $diff = abs($diff);

        $timestamp = new DateTime($timestamp);
        var_dump($timestamp->format(DATE_FORMAT));

        $results = $db->query("INSERT INTO matches (team_a, team_b, team_a_points, team_b_points, team_a_elo_new, team_b_elo_new, elo_diff, timestamp, updated, duration, entered_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
        "iiiiiiisiis", array($a, $b, $a_points, $b_points, $a_elo, $b_elo, $diff, $timestamp->format(DATE_FORMAT), $duration, $entered_by, "entered"));

        // Update team ELOs
        $team_a->elo = $a_elo;
        $team_b->elo = $b_elo;

        return new Match($db->getInsertId());
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
        $prob = 1.0 / (1 + 10 ^ (($b_elo-$a_elo)/400.0));
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

}
