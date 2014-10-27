<?php
/**
 * This file contains a class to quickly generate database queries for message
 *
 * @package    BZiON\Models\QueryBuilder
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * This class can be used to search for messages with specific characteristics
 * in the database.
 *
 * @package    BZiON\Models\QueryBuilder
 */
class MessageQueryBuilder extends QueryBuilder
{
    public function search($query)
    {
        $keywords = preg_split('/\s+/', trim($query));

        $query = "";

        $first = true;
        foreach ($keywords as $keyword) {
            if (!$first) {
                $query .= ' AND ';
            } else {
                $first = false;
            }

            $query .= "(message LIKE CONCAT('%', ?, '%'))";
            $this->parameters[] = $keyword;
            $this->types .= 's';
        }

        $this->conditions[] = $query;
        return $this;
    }
}
