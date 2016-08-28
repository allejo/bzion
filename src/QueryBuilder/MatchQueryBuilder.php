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
            $team_a_query = "FIND_IN_SET(?, team_a_players)";
            $team_b_query = "FIND_IN_SET(?, team_b_players)";
        } else {
            throw new InvalidArgumentException("Invalid model provided");
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
                $query = "($team_a_query OR $team_b_query) AND team_a_points = team_b_points";
                break;
            default:
                $query = "$team_a_query OR $team_b_query";
        }

        $this->conditions[] = $query;
        $this->parameters[] = $participant->getId();
        $this->parameters[] = $participant->getId();

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
     * @param Team $team The team in question
     * @return array
     */
    public function getSummary(Team $team)
    {
        $this->groupByMonth();

        $query = $this->createQuery("YEAR(timestamp) as y, MONTH(timestamp) as m, COUNT(*) as count");

        $matches = array();
        $results = Database::getInstance()->query($query, $this->parameters);

        foreach ($results as $match) {
            $matches[$match['y'] . '-' . sprintf('%02d', $match['m'])] = $match['count'];
        }

        // Add entries for dates with 0 matches
        $timestamp = $team->getCreationDate()->setTimezone('UTC')->startOfMonth();
        while ($timestamp->lte(TimeDate::now())) {
            $key = $timestamp->format('Y-m');
            if (!isset($matches[$key])) {
                $matches[$key] = 0;
            }

            $timestamp->addMonth();
        }
        ksort($matches);

        return $matches;
    }
}
