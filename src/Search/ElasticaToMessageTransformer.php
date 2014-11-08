<?php
/**
 * This file contains a class that converts Elasticsearch results into Models
 *
 * @license https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Search;

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Maps Elastica documents with BZIon models
 */
class ElasticaToMessageTransformer implements ElasticaToModelTransformerInterface
{
    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @param array $elasticaObjects of elastica objects
     * @throws \RuntimeException
     * @return array
     **/
    public function transform(array $elasticaObjects)
    {
        $objects = array();

        foreach ($elasticaObjects as $elasticaObject) {
            $objects[] = new \Message($elasticaObject->getId());
        }

        return $objects;
    }

    public function hybridTransform(array $elasticaObjects)
    {
        return $this->transform($elasticaObjects);
    }

    /**
     * Returns the object class used by the transformer.
     *
     * @return string
     */
    public function getObjectClass()
    {
        return '\Message';
    }

    /**
     * Returns the identifier field from the options
     *
     * @return string the identifier field
     */
    public function getIdentifierField()
    {
        return 'id';
    }
}
