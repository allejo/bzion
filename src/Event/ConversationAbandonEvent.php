<?php
/**
 * This file contains a conversation event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event dispatched whenever someone leaves a conversation
 */
class ConversationAbandonEvent extends Event
{
    /**
     * @var \Conversation
     */
    protected $conversation;

    /**
     * @var \Player|\Team
     */
    protected $member;

    /**
     * Create a new event
     *
     * @param \Conversation        $conversation  The conversation that the player left
     * @param \Player|\Team $member The member who abandoned the conversation
     */
    public function __construct(\Conversation $conversation, \Model $member)
    {
        $this->conversation = $conversation;
        $this->member = $member;
    }

    /**
     * Get the conversation that the player abandoned
     *
     * @return \Conversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * Get the member who left the conversation
     *
     * @return \Player|\Team
     */
    public function getMember()
    {
        return $this->member;
    }
}
