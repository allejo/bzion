<?php
/**
 * This file contains a class that responds to events
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Search;

use BZIon\Event\NewMessageEvent;
use Elastica\Type;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * An event subscriber for events that should update the Elasticsearch index
 */
class ElasticaEventSubscriber implements EventSubscriberInterface
{
    /**
     * Object persister
     *
     * @var ObjectPersister
     */
    protected $objectPersister;

    /**
     * Persister to Elasticsearch database for groups
     *
     * @var ObjectPersister
     */
    protected $groupPersister;

    /**
     * Persister to Elasticsearch database for messages
     */
    protected $messagePersister;

    /**
     * Constructor
     *
     * You will probably not need to instantiate an object of this class,
     * Symfony already does the hard work for us
     *
     * @param Type $groupType   The elasticsearch type for Groups
     * @param Type $messageType The elasticsearch type for Messages
     */
    public function __construct(Type $groupType, Type $messageType)
    {
        $groupTransformer = new GroupToElasticaTransformer();
        $messageTransformer = new MessageToElasticaTransformer();

        $this->groupPersister = new ObjectPersister($groupType, $groupTransformer, '\Group', array());
        $this->messagePersister = new ObjectPersister($messageType, $messageTransformer, '\Message', array());
    }

    /**
     * Returns all the events that this subscriber handles, and which method
     * handles each one
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        if (!\Service::getParameter('bzion.features.elasticsearch.enabled')) {
            // Don't subscribe to events if elasticsearch is disabled
            return array();
        }

        return array(
            'group.abandon' => 'update',
            'group.join'    => 'update',
            'group.kick'    => 'update',
            'message.new'   => 'onNew',
        );
    }

    /**
     * Update the elastica index when a Group gets updated
     *
     * @param Event $event The event
     */
    public function update(Event $event)
    {
        $this->groupPersister->replaceOne($event->getGroup());
    }

    /**
     * Update the elastica index when a new message is sent
     *
     * @param NewMessageEvent $event The event
     */
    public function onNew(NewMessageEvent $event)
    {
        if ($event->isFirst()) {
            // A new discussion was created, add it to the elasticsearch index
            $this->groupPersister->insertOne($event->getMessage()->getGroup());
        }

        $this->messagePersister->insertOne($event->getMessage());
    }
}
