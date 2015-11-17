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
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
     * Persister to Elasticsearch database for conversations
     *
     * @var ObjectPersister
     */
    protected $conversationPersister;

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
     * @param Type $conversationType   The elasticsearch type for Conversations
     * @param Type $messageType The elasticsearch type for Messages
     */
    public function __construct(Type $conversationType, Type $messageType)
    {
        $conversationTransformer = new ConversationToElasticaTransformer();
        $messageTransformer = new MessageToElasticaTransformer();

        $this->conversationPersister = new ObjectPersister($conversationType, $conversationTransformer, '\Conversation', array());
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
            'conversation.abandon' => 'update',
            'conversation.join'    => 'update',
            'conversation.kick'    => 'update',
            'message.new'          => 'onNew',
        );
    }

    /**
     * Update the elastica index when a Conversation gets updated
     *
     * @param Event $event The event
     */
    public function update(Event $event)
    {
        $this->conversationPersister->replaceOne($event->getConversation());
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
            $this->conversationPersister->insertOne($event->getMessage()->getConversation());
        }

        $this->messagePersister->insertOne($event->getMessage());
    }
}
