<?php
/**
 * This file contains a conversation event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

/**
 * Event thrown when players join a conversation
 */
class ConversationJoinEvent extends Event
{
    /**
     * @var \Conversation
     */
    protected $conversation;

    /**
     * @var \Model[]
     */
    protected $members;

    /**
     * Create a new event
     *
     * @param \Conversation   $conversation   The conversation in question
     * @param \Model[] $members The players and teams who joined the conversation
     */
    public function __construct(\Conversation $conversation, array $members)
    {
        $this->conversation  = $conversation;
        $this->members = $members;
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
     * Get the Players and Teams who joined the conversation
     *
     * @return \Model[]
     */
    public function getNewMembers()
    {
        return $this->members;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $players = $teams = array();

        foreach ($this->members as $member) {
            if ($member instanceof \Player) {
                $players[] = $member;
            } else {
                $teams[] = $member;
            }
        }

        return serialize(array(
            'conversation' => $this->conversation->getId(),
            'players'      => \Player::mapToIDs($players),
            'teams'        => \Team::mapToIDs($teams)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $conversation = \Conversation::get($data['conversation']);

        $players = \Player::arrayIdToModel($data['players']);
        $teams = \Team::arrayIdToModel($data['teams']);

        $this->__construct($conversation, array_merge($players, $teams));
    }
}
