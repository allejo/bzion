<?php
/**
 * This file contains a conversation event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event thrown when a conversation gets renamed
 */
class ConversationRenameEvent extends Event
{
    /**
     * @var \Conversation
     */
    protected $conversation;

    /**
     * @var string
     */
    protected $oldSubject;

    /**
     * @var string
     */
    protected $newSubject;

    /**
     * @var \Player
     */
    protected $player;

    /**
     * Create a new event
     *
     * @param \Conversation  $conversation      The conversation in question
     * @param string  $oldSubject The old name of the Conversation
     * @param string  $newSubject The new name of the conversation
     * @param \Player $player     The player who made the change
     */
    public function __construct(\Conversation $conversation, $oldSubject, $newSubject, \Player $player)
    {
        $this->conversation = $conversation;
        $this->oldSubject = $oldSubject;
        $this->newSubject = $newSubject;
        $this->player = $player;
    }

    /**
     * Get the conversation that was renamed
     *
     * @return \Conversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * Get the Player who renamed the conversation
     *
     * @return \Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Get the old name of the conversation
     *
     * @return string
     */
    public function getOldSubject()
    {
        return $this->oldSubject;
    }

    /**
     * Get the new name of the conversation
     *
     * @return string
     */
    public function getNewSubject()
    {
        return $this->newSubject;
    }
}
