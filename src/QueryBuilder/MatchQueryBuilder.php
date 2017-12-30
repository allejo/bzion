<?php
/**
 * This file contains a class to quickly generate database queries for matches
 *
 * @package    BZiON\Models\QueryBuilder
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * This class can be used to search for matches with specific characteristics in
 * the database.
 *
 * @package    BZiON\Models\QueryBuilder
 */
class MatchQueryBuilder extends QueryBuilder
{
    /**
     * Only include matches where a specific team/player played
     *
     * @param  Team|Player $participant The team/player which played the matches
     * @param  string      $result      The outcome of the matches (win, draw or loss)
     * @return self
     */
    public function with($participant, $result = null)
    {
        if (!$participant || !$participant->isValid()) {
            return $this;
        }

        if ($participant instanceof Team) {
            $team_a_query = "team_a = ?";
            $team_b_query = "team_b = ?";
        } elseif ($participant instanceof Player) {
            $this->extras = 'INNER JOIN match_participation mp ON mp.match_id = matches.id';
            $team_a_query = $team_b_query = '? = mp.user_id';
        } else {
            throw new InvalidArgumentException("Invalid model provided");
        }

        if ($team_a_query === $team_b_query) {
            $team_query_or = $team_a_query;
            $this->parameters[] = $participant->getId();
        } else {
            $team_query_or = "$team_a_query OR $team_b_query";
            $this->parameters[] = $participant->getId();
            $this->parameters[] = $participant->getId();
        }

        switch ($result) {
            case "wins":
            case "win":
            case "victory":
            case "victories":
                $query = "($team_a_query AND team_a_points > team_b_points) OR ($team_b_query AND team_b_points > team_a_points)";
                break;
            case "loss":
            case "lose":
            case "losses":
            case "defeat":
            case "defeats":
                $query = "($team_a_query AND team_b_points > team_a_points) OR ($team_b_query AND team_a_points > team_b_points)";
                break;
            case "draw":
            case "draws":
            case "tie":
            case "ties":
                $query = "($team_query_or) AND team_a_points = team_b_points";
                break;
            default:
                $query = "$team_query_or";
        }

        $this->whereConditions[] = $query;

        return $this;
    }

    /**
     * Group results by day
     *
     * @return $this
     */
    public function groupByMonth()
    {
        $this->groupQuery .= "GROUP BY YEAR(timestamp), MONTH(timestamp)";

        return $this;
    }

    /**
     * Get a count for each month's matches
     *
     * @param TimeDate $timeDate The team in question
     *
     * @return array
     */
    public function getSummary(TimeDate $timeDate)
    {
        $this->groupQuery = 'GROUP BY match_date';

        $query = $this->createQuery("DATE_FORMAT(timestamp, '%Y-%m') AS match_date, COUNT(*) as match_count");

        $matches = [];
        $results = Database::getInstance()->query($query, $this->parameters);

        foreach ($results as $match) {
            $matches[$match['match_date']] = $match['match_count'];
        }

        $interval = new DateInterval('P1M');
        $dateRange = new DatePeriod($timeDate, $interval, TimeDate::now());

        /** @var DateTime $month */
        foreach($dateRange as $month) {
            $key = $month->format('Y-m');

            if (!isset($matches[$key])) {
                $matches[$key] = 0;
            }
        }

        ksort($matches);

        return $matches;
    }
}
