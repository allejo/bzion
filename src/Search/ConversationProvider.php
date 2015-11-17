<?php
/**
 * This file contains a content provider for the Elasticsearch bundle
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Search;

use Elastica\Type;
use FOS\ElasticaBundle\Provider\ProviderInterface;

/**
 * Stores conversations to be managed by Elasticsearch
 */
class ConversationProvider implements ProviderInterface
{
    /**
     * The elastica type of the Conversation
     *
     * @var Type
     */
    protected $conversationType;

    /**
     * The transformer that converts our models to elastica objects
     *
     * @var ConversationToElasticaTransformer
     */
    protected $transformer;

    /**
     * Load the dependencies for the MessageProvider
     *
     * @param Type $conversationType The elastica type
     */
    public function __construct(Type $conversationType)
    {
        $this->conversationType = $conversationType;
        $this->transformer = new ConversationToElasticaTransformer();
    }

    /**
     * Insert the repository objects in the type index
     *
     * @param \Closure $loggerClosure A logging function
     * @param array    $options
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        if ($loggerClosure) {
            $loggerClosure('Indexing conversations');
        }

        $conversations = \Conversation::getQueryBuilder()->active()->getModels();
        $documents = array();

        foreach ($conversations as $conversation) {
            $documents[] = $this->transformer->transform($conversation, array());
        }

        $this->conversationType->addDocuments($documents);
    }
}
