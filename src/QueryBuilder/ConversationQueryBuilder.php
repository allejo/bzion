<?php
/**
 * This file contains a class to quickly generate database queries for conversations
 *
 * @package    BZiON\Models\QueryBuilder
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * This class can be used to search for conversations  with specific
 * characteristics in the database.
 *
 * @package    BZiON\Models\QueryBuilder
 */
class ConversationQueryBuilder extends QueryBuilder
{
     /**
     * Only return messages that are sent from/to a specific player
     *
     * @param  Player $player The player related to the messages
     * @return self
     */
    public function forPlayer($player)
    {
        $this->extras .= '
            LEFT JOIN player_conversations ON player_conversations.conversation=conversations.id
        ';

        $this->column('player_conversations.player')->is($player);
        $this->column('conversations.status')->isOneOf(Conversation::getActiveStatuses());

        return $this;
    }

}
