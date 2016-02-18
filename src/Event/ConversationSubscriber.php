<?php
/**
 * This file contains a class that responds to events
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

use BZIon\NotificationAdapter\WebSocketAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * An event subscriber for events related to conversations
 */
class ConversationSubscriber implements EventSubscriberInterface
{
    /**
     * Returns all the events that this subscriber handles, and which method
     * handles each one
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'team.abandon' => 'onTeamMembershipChange',
            'team.kick'    => 'onTeamMembershipChange',
            'team.join'    => 'onTeamMembershipChange',
        );
    }

    /**
     * Called every time a member is added/removed from a team
     *
     * @param TeamAbandonEvent|TeamJoinEvent|TeamKickEvent $event The event
     * @param string $type The type of the event
     */
    public function onTeamMembershipChange(Event $event, $type)
    {
        $query = \Conversation::getQueryBuilder()->forTeam($event->getTeam());

        foreach ($query->getModels() as $conversation) {
            \ConversationEvent::storeEvent($conversation->getId(), $event, $type);
        }
    }
}
