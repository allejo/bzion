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
     * Only include matches where a specific team played
     *
     * @param  $team   Team        The team which played the matches
     * @param  $result string|null The outcome of the matches (win, draw or loss)
     * @return self
     */
    public function with($team, $result=null)
    {
        if (!$team || !$team->isValid()) {
            return $this;
        }

        switch ($result) {
        case "wins":
        case "win":
        case "victory":
        case "victories":
            $query = "(team_a = ? AND team_a_points > team_b_points) OR (team_b = ? AND team_b_points > team_a_points)";
            break;
        case "loss":
        case "lose":
        case "losses":
        case "defeat":
        case "defeats":
            $query = "(team_a = ? AND team_b_points > team_a_points) OR (team_b = ? AND team_a_points > team_b_points)";
            break;
        case "draw":
        case "draws":
        case "tie":
        case "ties":
            $query = "((team_a = ? OR team_b = ?) AND team_a_points = team_b_points)";
            break;
        default:
            $query = "team_a = ? OR team_b = ?";
        }

        $this->conditions[] = $query;
        $this->parameters[] = $team->getId();
        $this->parameters[] = $team->getId();
        $this->types       .= 'ii';

        return $this;
    }
}
