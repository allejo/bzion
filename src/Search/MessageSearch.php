<?php
/**
 * This file contains a class that performs message searches
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Search;

use FOS\ElasticaBundle\Provider\ProviderInterface;
use Elastica\Type;
use Elastica\Document;

/**
 * Performs a search on messages
 */
class MessageSearch {
    /**
     * The MySQL query builder for messages
     * @var \MessageQueryBuilder
     */
    private $queryBuilder;

    /**
     * Create a new message search
     *
     * @param MessageQueryBuilder $queryBuilder The MySQL query builder for messages
     */
    public function __construct(\MessageQueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Perform a search on messages and get the results
     *
     * @param  string $query The query string
     * @return Message[] The results of the search
     */
    public function search($query)
    {
        \Debug::startStopwatch('search.messages');

        if (\Service::getParameter('bzion.features.elasticsearch.enabled')) {
            $results = $this->elasticSearch($query);
        } else {
            $results = $this->mysqlSearch($query);
        }

        \Debug::finishStopwatch('search.messages');

        return $results;
    }

    /**
     * Perform a search on messages using Elasticsearch
     *
     * @param  string $query The query string
     * @return Message[] The results of the search
     */
    private function elasticSearch($query)
    {
        $finder = \Service::getContainer()->get('fos_elastica.finder.search');

        // We have only stored "active" messages and groups on Elasticsearch's
        // database, so there is no check for that again
        $results = $finder->find($query);

        return $results;

    }

    /**
     * Perform a search on messages using the data stored in the MySQL database
     *
     * @param  string $query The query string
     * @return Message[] The results of the search
     */
    private function mysqlSearch($search)
    {
        return $this->queryBuilder
            ->search($search)
            ->getModels();
    }
}
