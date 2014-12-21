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
 * Stores groups to be managed by Elasticsearch
 */
class GroupProvider implements ProviderInterface
{
    /**
     * The elastica type of the Group
     *
     * @var Type
     */
    protected $groupType;

    /**
     * The transformer that converts our models to elastica objects
     *
     * @var GroupToElasticaTransformer
     */
    protected $transformer;

    /**
     * Load the dependencies for the MessageProvider
     *
     * @param Type $groupType The elastica type
     */
    public function __construct(Type $groupType)
    {
        $this->groupType = $groupType;
        $this->transformer = new GroupToElasticaTransformer();
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
            $loggerClosure('Indexing groups');
        }

        $groups = \Group::getQueryBuilder()->active()->getModels();
        $documents = array();

        foreach ($groups as $group) {
            $documents[] = $this->transformer->transform($group, array());
        }

        $this->groupType->addDocuments($documents);
    }
}
