<?php
/**
 * This file contains a class to quickly generate database queries for visits
 *
 * @package    BZiON\Models\QueryBuilder
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * This class can be used to search for visits with specific characteristics in
 * the database.
 *
 * @package    BZiON\Models\QueryBuilder
 */
class VisitQueryBuilder extends QueryBuilder
{
    /**
     * Search for a visit from the given IP or Host
     *
     * @param  string $query An IP or host to search
     * @return self
     */
    public function search($query)
    {
        $this->whereConditions[] = "(ip LIKE CONCAT('%', ?, '%') OR host LIKE CONCAT('%', ?, '%'))";

        $this->parameters[] = $query;
        $this->parameters[] = $query;
//        $this->parameters[] = $query;

        return $this;
    }
}
