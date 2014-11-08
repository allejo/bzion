<?php
/**
 * This file contains a content provider for the Elasticsearch bundle
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Search;

use FOS\ElasticaBundle\Provider\ProviderInterface;
use Elastica\Type;
use Elastica\Document;

/**
 * Stores messages so that elastica can read them
 */
class MessageProvider implements ProviderInterface
{
    /**
     * The elastica type of the Message
     *
     * @var Type
     */
    protected $messageType;

    /**
     * The transformer that converts our models to elastica objects
     *
     * @var MessageToElasticaTransformer
     */
    protected $transformer;

    /**
     * Load the dependencies for the MessageProvider
     *
     * @param Type $messageType The elastica type
     */
    public function __construct(Type $messageType)
    {
        $this->messageType = $messageType;
        $this->transformer = new MessageToElasticaTransformer();
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
            $loggerClosure('Indexing messages');
        }

        $messages = \Message::getQueryBuilder()->active()->getModels();
        $documents = array();

        foreach ($messages as $message) {
            $documents[] = $this->transformer->transform($message);
        }

        $this->messageType->addDocuments($documents);
    }
}
