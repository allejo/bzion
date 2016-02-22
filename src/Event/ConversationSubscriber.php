<?php
/**
 * This file contains a class that responds to events
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

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
            'team.abandon' => array(
                array('onTeamMembershipChange'),
                array('onTeamLeave')
            ),
            'team.kick' => array(
                array('onTeamMembershipChange'),
                array('onTeamLeave')
            ),
            'team.join' => 'onTeamMembershipChange',
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

            if ($type === 'team.join') {
                $conversation->addMember($event->getPlayer(), $distinct = false);
            }
        }
    }

    /**
     * When a player leaves a team, remove them from every conversation that
     * includes that team
     *
     * @param TeamAbandonEvent|TeamKickEvent $event The event
     */
    public function onTeamLeave(Event $event)
    {
        // We don't need to check which conversations include the player; a
        // player_conversations entry will have `distinct` set to 0 only if the
        // player belongs to a conversation because they are a member of this
        // team
        \Database::getInstance()->query(
            "DELETE FROM `player_conversations`
                WHERE player = ?
                AND `distinct` = 0", "i", array($event->getPlayer()->getId())
        );
    }
}
