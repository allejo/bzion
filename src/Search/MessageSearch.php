<?php
/**
 * This file contains a class that performs message searches
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Search;

use BZIon\Debug\Debug;
use Elastica\Query\Bool;
use Elastica\Query\HasParent;
use Elastica\Query\Match;
use Elastica\Query\Term;

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
     * @param MessageQueryBuilder $queryBuilder The MySQL query builder for messages
     * @param Player|null         $player       The player to make the search for
     */
    public function __construct(\MessageQueryBuilder $queryBuilder, \Player $player = null)
    {
        $this->queryBuilder = $queryBuilder;
        $this->player = $player;
    }

    /**
     * Perform a search on messages and get the results
     *
     * @param  string    $query The query string
     * @return Message[] The results of the search
     */
    public function search($query)
    {
        Debug::startStopwatch('search.messages');

        if (\Service::getParameter('bzion.features.elasticsearch.enabled')) {
            $results = $this->elasticSearch($query);
        } else {
            $results = $this->mysqlSearch($query);
        }

        Debug::finishStopwatch('search.messages');

        return $results;
    }

    /**
     * Perform a search on messages using Elasticsearch
     *
     * @param  string    $query The query string
     * @return Message[] The results of the search
     */
    private function elasticSearch($query)
    {
        $finder = \Service::getContainer()->get('fos_elastica.finder.search');
        $boolQuery = new BoolQuery();

        // We have only stored "active" messages and groups on Elasticsearch's
        // database, so there is no check for that again
        if ($this->player) {
            // Make sure that the parent of the message (i.e. the group that the
            // message belongs into) has the current player as its member
            $recipientQuery = new Term();
            $recipientQuery->setTerm('members', $this->player->getId());
            $parentQuery = new HasParent($recipientQuery, 'group');
            $boolQuery->addMust($parentQuery);
        }

        $fieldQuery = new Match();
        $fieldQuery->setFieldQuery('content', $query)
                   ->setFieldFuzziness('content', 'auto');
        $boolQuery->addMust($fieldQuery);

        return $finder->find($boolQuery);
    }

    /**
     * Perform a search on messages using the data stored in the MySQL database
     *
     * @param  string    $query The query string
     * @return Message[] The results of the search
     */
    private function mysqlSearch($search)
    {
        return $this->queryBuilder
            ->search($search)
            ->forPlayer($this->player)
            ->getModels();
    }
}
