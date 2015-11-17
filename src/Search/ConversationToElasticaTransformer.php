<?php
/**
 * This file contains a class that converts Models into Elasticsearch results
 *
 * @license https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Search;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

/**
 * Maps Elastica documents with BZIon models
 */
class ConversationToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * Transforms an object into an elastica object
     *
     * @param \Conversation $conversation  the object to convert
     * @param array  $fields the keys we want to have in the returned array
     *
     * @return Document
     **/

    public function transform($conversation, array $fields = array())
    {
        return new Document(
            $conversation->getId(),
            array(
                'members' => $conversation->getPlayerIDs()
            )
        );
    }
}
