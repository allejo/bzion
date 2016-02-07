<?php
/**
 * This file contains functionality relating to the participants of a conversation message
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A discussion (group of messages)
 * @package    BZiON\Models
 */
class Conversation extends UrlModel implements NamedModel
{
    /**
     * The subject of the conversation
     * @var string
     */
    protected $subject;

    /**
     * The time of the last message to the conversation
     * @var TimeDate
     */
    protected $last_activity;

    /**
     * The id of the creator of the conversation
     * @var int
     */
    protected $creator;

    /**
     * The status of the conversation
     *
     * Can be 'active', 'disabled', 'deleted' or 'reported'
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "conversations";

    /**
     * {@inheritdoc}
     */
    protected function assignResult($conversation)
    {
        $this->subject = $conversation['subject'];
        $this->last_activity = TimeDate::fromMysql($conversation['last_activity']);
        $this->creator = $conversation['creator'];
        $this->status = $conversation['status'];
    }

    /**
     * Get the subject of the discussion
     *
     * @return string
     **/
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the creator of the discussion
     *
     * @return Player
     */
    public function getCreator()
    {
        return Player::get($this->creator);
    }

    /**
     * Determine whether a player is the one who created the message conversation
     *
     * @param  int  $id The ID of the player to test for
     * @return bool
     */
    public function isCreator($id)
    {
        return ($this->creator == $id);
    }

    /**
     * {@inheritdoc}
     */
    public function isEditor ($player)
    {
        return $this->isCreator($player->getId());
    }

    /**
     * Get the time when the conversation was most recently active
     *
     * @return TimeDate
     */
    public function getLastActivity()
    {
        return $this->last_activity;
    }

    /**
     * Update the conversation's last activity timestamp
     *
     * @return void
     */
    public function updateLastActivity()
    {
        $this->last_activity = TimeDate::now();
        $this->update('last_activity', $this->last_activity->toMysql(), 's');
    }

    /**
     * Update the conversation's subject
     *
     * @param  string $subject The new subject
     * @return self
     */
    public function setSubject($subject)
    {
        return $this->updateProperty($this->subject, 'subject', $subject, 's');
    }

    /**
     * Get the last message of the conversation
     *
     * @return Message
     */
    public function getLastMessage()
    {
        $ids = self::fetchIdsFrom('conversation_to', array($this->id), 'i', false, 'AND event_type IS null ORDER BY id DESC LIMIT 0,1', 'messages');

        if (!isset($ids[0])) {
            return Message::invalid();
        }

        return Message::get($ids[0]);
    }

    /**
     * Find whether the last message in the conversation has been read by a player
     *
     * @param  int     $playerId The ID of the player
     * @return bool
     */
    public function isReadBy($playerId)
    {
        $query = $this->db->query("SELECT `read` FROM `player_conversations` WHERE `player` = ? AND `conversation` = ?",
            'ii', array($playerId, $this->id));

        return ($query[0]['read'] == 1);
    }

    /**
     * Mark the last message in the conversation as having been read by a player
     *
     * @param  int  $playerId The ID of the player
     * @return void
     */
    public function markReadBy($playerId)
    {
        $this->db->query(
            "UPDATE `player_conversations` SET `read` = 1 WHERE `player` = ? AND `conversation` = ? AND `read` = 0",
            'ii', array($playerId, $this->id)
        );
    }

    /**
     * Mark the last message in the conversation as unread by the conversation's members
     *
     * @param  int  $except The ID of a player to exclude
     * @return void
     */
    public function markUnread($except)
    {
        $this->db->query(
            "UPDATE `player_conversations` SET `read` = 0 WHERE `conversation` = ? AND `player` != ?",
            'ii',
            array($this->id, $except)
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getRouteName($action = 'show')
    {
        return "message_conversation_$action";
    }

    /**
     * {@inheritdoc}
     */
    public static function getActiveStatuses()
    {
        return array('active', 'reported');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getSubject();
    }

    /**
     * Get a list containing each member of the conversation
     * @param  int|null $hide The ID of a player to ignore
     * @return Model[]  An array of players and teams
     */
    public function getMembers($hide = null)
    {
        $members = Player::arrayIdToModel($this->getPlayerIds($hide, true));
        usort($members, Player::getAlphabeticalSort());

        $teams = Team::arrayIdToModel($this->getTeamIds());
        usort($teams, Team::getAlphabeticalSort());

        return array_merge($members, $teams);
    }

    /**
     * Get a list containing the IDs of each member player of the conversation
     * @param  int|null  $hide     The ID of a player to ignore
     * @param  bool   $distinct Whether to only return players who were
     *                             specifically invited to the conversation, and
     *                             are not participating only as members of a team
     * @return integer[] An array of player IDs
     */
    public function getPlayerIds($hide = null, $distinct = false)
    {
        $additional_query = "WHERE `conversation` = ?";
        $types = "i";
        $params = array($this->id);

        if ($hide) {
            $additional_query .= " AND `player` != ?";
            $types .= "i";
            $params[] = $hide;
        }

        if ($distinct) {
            $additional_query .= " AND `distinct` = 1";
        }

        return parent::fetchIds($additional_query, $types, $params, "player_conversations", "player");
    }

    /**
     * Get a list containing the IDs of each member team of the conversation
     *
     * @return integer[] An array of team IDs
     */
    public function getTeamIds()
    {
        return parent::fetchIds("WHERE `conversation` = ?", "i", $this->id, "team_conversations", "team");
    }

    /**
     * Create a new message conversation
     **
     * @param  string $subject   The subject of the conversation
     * @param  int    $creatorId The ID of the player who created the conversation
     * @param  array  $members   A list of Models representing the conversation's members
     * @return Conversation  An object that represents the created conversation
     */
    public static function createConversation($subject, $creatorId, $members = array())
    {
        $conversation = self::create(array(
            'subject' => $subject,
            'creator' => $creatorId,
            'status'  => "active",
        ), 'sis', 'last_activity');

        foreach ($members as $member) {
            $conversation->addMember($member);
        }

        return $conversation;
    }

    /**
     * Send a new message to the conversation's members
     * @param  Player  $from    The sender
     * @param  string  $message The body of the message
     * @param  string  $status  The status of the message - can be 'visible', 'hidden', 'deleted' or 'reported'
     * @return Message An object that represents the sent message
     */
    public function sendMessage($from, $message, $status = 'visible')
    {
        $message = Message::sendMessage($this->getId(), $from->getId(), $message, $status);

        $this->updateLastActivity();

        return $message;
    }

    /**
     * Checks if a player or team belongs in the conversation
     * @param  Player|Team $member The player or team to check
     * @return bool True if the given object belongs in the conversation, false if they don't
     */
    public function isMember($member)
    {
        $type = ($member instanceof Player) ? 'player' : 'team';

        $result = $this->db->query("SELECT 1 FROM `{$type}_conversations` WHERE `conversation` = ?
                                    AND `$type` = ?", "ii", array($this->id, $member->getId()));

        return count($result) > 0;
    }

    /**
     * Add a member to the discussion
     *
     * @param  Player|Team $member The member  to add
     * @return void
     */
    public function addMember($member)
    {
        if ($member instanceof Player) {
            // Mark individual players as distinct by creating or updating the
            // entry on the table
            $this->db->query(
                "INSERT INTO `player_conversations` (`conversation`, `player`, `distinct`) VALUES (?, ?, 1)
                    ON DUPLICATE KEY UPDATE `distinct` = 1",
                "ii",
                array($this->getId(), $member->getId())
            );
        } elseif ($member instanceof Team) {
            // Add the team to the team_conversations table...
            $this->db->query(
                "INSERT INTO `team_conversations` (`conversation`, `team`) VALUES (?, ?)",
                "ii",
                array($this->getId(), $member->getId())
            );

            // ...and each of its members in the player_conversations table as
            // non-distinct (unless they were already there)
            foreach ($member->getMembers() as $player) {
                $this->db->query(
                    "INSERT IGNORE INTO `player_conversations` (`conversation`, `player`, `distinct`) VALUES (?, ?, 0)",
                    "ii",
                    array($this->getId(), $player->getId())
                );
            }
        }
    }

    /**
     * Remove a member from the discussion
     *
     * @todo
     *
     * @param  Player|Team $member The member to remove
     * @return void
     */
    public function removeMember($member)
    {
        if ($member instanceof Player) {
            $this->db->query("DELETE FROM `player_conversations` WHERE `conversation` = ? AND `player` = ?", "ii", array($this->getId(), $member->getId()));
        } else {
            throw new Exception("Not implemented yet");
        }
    }

    /**
     * Find out which members of the conversation should receive an e-mail after a new
     * message has been sent
     *
     * @param  int   $except The ID of a player who won't receive an e-mail (e.g. message author)
     * @return int[] A player ID list
     */
    public function getWaitingForEmailIDs($except)
    {
        return $this->fetchIds(
            'LEFT JOIN players ON pg.player = players.id WHERE pg.conversation = ? AND pg.read = 1 AND pg.player != ?  AND players.verified = 1 AND players.receives != "nothing"',
            'ii',
            array($this->id, $except),
            'player_conversations AS pg',
            'pg.player');
    }

    /**
     * Get a query builder for conversations
     * @return ConversationQueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new ConversationQueryBuilder('Conversation', array(
            'columns' => array(
                'last_activity' => 'last_activity',
                'status' => 'status'
            ),
            'name' => 'subject',
        ));
    }
}
