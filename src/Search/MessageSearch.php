<?php
/**
 * This file contains a class that performs message searches
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Search;

use BZIon\Debug\Debug;

/**
 * Performs a search on messages
 */
class MessageSearch
{
    /**
     * The MySQL query builder for messages
     * @var \MessageQueryBuilder
     */
    private $queryBuilder;

    /**
     * The user to whom the returned messages will be visible
     * @var \Player|null
     */
    private $player;

    /**
     * Create a new message search
     *
     * @param \MessageQueryBuilder $queryBuilder The MySQL query builder for messages
     * @param \Player|null         $player       The player to make the search for
     */
    public function __construct(\MessageQueryBuilder $queryBuilder, \Player $player = null)
    {
        $this->queryBuilder = $queryBuilder;
        $this->player = $player;
    }

    /**
     * Perform a search on messages and get the results
     *
     * @param  string     $query The query string
     * @return \Message[] The results of the search
     */
    public function search($query)
    {
        Debug::startStopwatch('search.messages');

        $results = $this->mysqlSearch($query);

        Debug::finishStopwatch('search.messages');

        return $results;
    }

    /**
     * Perform a search on messages using the data stored in the MySQL database
     *
     * @param  string     $query The query string
     * @return \Message[] The results of the search
     */
    private function mysqlSearch($query)
    {
        return $this->queryBuilder
            ->search($query)
            ->forPlayer($this->player)
            ->getModels();
    }
}
