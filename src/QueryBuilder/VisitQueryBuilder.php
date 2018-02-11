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
class VisitQueryBuilder extends QueryBuilderFlex
{
    /**
     * Search for a visit from the given IP or Host
     *
     * @param  string $query An IP or host to search
     *
     * @return static
     */
    public function search($query)
    {
        $this->where(function ($qb) use ($query) {
            /** @var static $qb */
            $qb->where('ip', 'LIKE', "%$query%");
            $qb->orWhere('host', 'LIKE', "%$query%");
        });

        return $this;
    }
}
