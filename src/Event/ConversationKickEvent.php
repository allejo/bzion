<?php
/**
 * This file contains a conversation event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event announced when someone a player is kicked from a conversation
 */
class ConversationKickEvent extends Event
{
    /**
     * @var \Conversation
     */
    protected $conversation;

    /**
     * @var \Player|\Team
     */
    protected $kicked;

    /**
     * @var \Player
     */
    protected $kicker;

    /**
     * Create a new event
     *
     * @param \Conversation        $conversation  The conversation from which the player was kicked
     * @param \Player|\Team $kicked The member who was kicked
     * @param \Player       $kicker The player who issued the kick
     */
    public function __construct(\Conversation $conversation, \Model $kicked, \Player $kicker)
    {
        $this->conversation = $conversation;
        $this->kicked = $kicked;
        $this->kicker = $kicker;
    }

    /**
     * Get the conversation from which the player was kicked
     *
     * @return \Conversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * Get the member who was kicked
     *
     * @return \Player|\Team
     */
    public function getKicked()
    {
        return $this->kicked;
    }

    /**
     * Get the member who was kicked
     *
     * Alias for ConversationKickEvent::getKicked()
     *
     * @return \Player|\Team
     */
    public function getMember()
    {
        return $this->kicked;
    }

    /**
     * Get the player who issued the kick
     *
     * @return \Player
     */
    public function getKicker()
    {
        return $this->kicker;
    }
}
