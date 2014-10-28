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
 * Stores groups to be managed by Elasticsearch
 */
class GroupProvider implements ProviderInterface
{
    /**
     * The elastica type of the Group
     * @var Type
     */
    protected $groupType;

    /**
     * Load the dependencies for the MessageProvider
     *
     * @param Type $groupType The elastica type
     */
    public function __construct(Type $groupType)
    {
        $this->groupType = $groupType;
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
            $data = array(
                'members' => $group->getMemberIDs()
            );

            $documents[] = new Document($group->getId(), $data);
        }

        $this->groupType->addDocuments($documents);
    }
}
