<?php
/**
 * This file contains a list of events that may or may not happen during a request
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event list
 */
class Events
{
    /**
     * The conversation abandon event is thrown when a player leaves a conversation
     *
     * The event listener receives a BZIon\Event\ConversationAbandonEvent instance
     *
     * @var string
     */
    const CONVERSATION_ABANDON = 'conversation.abandon';

    /**
     * The conversation join event is announced whenever new players join a conversation
     *
     * The event listener receives a BZIon\Event\ConversationJoinEvent instance
     *
     * @var string
     */
    const CONVERSATION_JOIN = 'conversation.join';

    /**
     * The conversation kick event is thrown every time a player gets kicked from a
     * conversation
     *
     * The event listener receives a BZIon\Event\ConversationKickEvent instance
     *
     * @var string
     */
    const CONVERSATION_KICK = 'conversation.kick';

    /**
     * The conversation rename event is dispatched each time a new notification is sent
     * to a player
     *
     * The event listener receives a BZIon\Event\ConversationRenameEvent instance
     *
     * @var string
     */
    const CONVERSATION_RENAME = 'conversation.rename';

    /**
     * The message event is thrown each time a new message is sent or a
     * conversation is created
     *
     * The event listener receives a BZIon\Event\NewMessageEvent instance
     *
     * @var string
     */
    const MESSAGE_NEW = 'message.new';

    /**
     * The new notification event is dispatched each time a new notification is
     * sent to a player
     *
     * The event listener receives a BZIon\Event\NewNotificationEvent instance
     *
     * @var string
     */
    const NOTIFICATION_NEW = 'notification.new';

    /**
     * The team abandon event is announced every time a player leaves a team
     *
     * The event listener receives a BZIon\Event\TeamAbandonEvent instance
     *
     * @var string
     */
    const TEAM_ABANDON = 'team.abandon';

    /**
     * The team delete event is sent when a team is deleted
     *
     * The event listener receives a BZIon\Event\TeamDeleteEvent instance
     *
     * @var string
     */
    const TEAM_DELETE = 'team.delete';

    /**
     * The team invite event is dispatched whenever a player is invited
     * to a team
     *
     * The event listener receives a BZIon\Event\TeamInviteEvent instance
     *
     * @var string
     */
    const TEAM_INVITE = 'team.invite';

    /**
     * The team join event is dispatched when a player becomes a member of a
     * team
     *
     * The event listener receives a BZIon\Event\TeamJoinEvent instance
     *
     * @var string
     */
    const TEAM_JOIN = 'team.join';

    /**
     * The team kick event is announced every time a player gets kicked from a
     * team
     *
     * The event listener receives a BZIon\Event\TeamKickEvent instance
     *
     * @var string
     */
    const TEAM_KICK = 'team.kick';

    /**
     * The team leader change event is sent whenever a player is assigned to be
     * the new leader of a team
     *
     * The event listener receives a BZIon\Event\TeamLeaderChangeEvent instance
     *
     * @var string
     */
    const TEAM_LEADER_CHANGE = 'team.leader_change';

    /**
     * The welcome event is sent when a new player is added to the database
     *
     * The event listener receives a BZIon\Event\WelcomeEvent instance
     *
     * @var string
     */
    const WELCOME = 'welcome';
}
