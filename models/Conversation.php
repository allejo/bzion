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
        return $this->creator == $id;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditor($player)
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
        return $this->last_activity->copy();
    }

    /**
     * Update the conversation's last activity timestamp
     *
     * @return void
     */
    public function updateLastActivity()
    {
        $this->last_activity = TimeDate::now();
        $this->update('last_activity', $this->last_activity->toMysql());
    }

    /**
     * Update the conversation's subject
     *
     * @param  string $subject The new subject
     * @return self
     */
    public function setSubject($subject)
    {
        return $this->updateProperty($this->subject, 'subject', $subject);
    }

    /**
     * Get the last message of the conversation
     *
     * @return Message
     */
    public function getLastMessage()
    {
        $ids = self::fetchIdsFrom('conversation_to', array($this->id), false, 'AND event_type IS null ORDER BY id DESC LIMIT 0,1', 'messages');

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
            array($playerId, $this->id));

        return $query[0]['read'] == 1;
    }

    /**
     * Mark the last message in the conversation as having been read by a player
     *
     * @param  int  $playerId The ID of the player
     * @return void
     */
    public function markReadBy($playerId)
    {
        $this->db->execute(
            "UPDATE `player_conversations` SET `read` = 1 WHERE `player` = ? AND `conversation` = ? AND `read` = 0",
            array($playerId, $this->id)
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
        $this->db->execute(
            "UPDATE `player_conversations` SET `read` = 0 WHERE `conversation` = ? AND `player` != ?",
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
     * Get the members of one of the conversation's teams that don't belong in
     * the conversation
     *
     * @todo   Use Model::createFromDatabaseResults()
     * @param  Team $team The team to check
     * @return Player[]
     */
    public function getMissingTeamMembers(Team $team)
    {
        $query = "SELECT players.id AS id FROM players
            WHERE players.team = ?
            AND players.id NOT IN (
              SELECT player_conversations.player FROM player_conversations
              WHERE player_conversations.conversation = ?
            )";

        $results = $this->db->query($query, array($team->getId(), $this->id));

        return Player::arrayIdToModel(array_column($results, 'id'));
    }

    /**
     * Get a list containing the IDs of each member player of the conversation
     * @param  int|null  $hide     The ID of a player to ignore
     * @param  bool   $distinct Whether to only return players who were
     *                             specifically invited to the conversation, and
     *                             are not participating only as members of a team
     * @return int[] An array of player IDs
     */
    public function getPlayerIds($hide = null, $distinct = false)
    {
        $additional_query = "WHERE `conversation` = ?";
        $params = array($this->id);

        if ($hide) {
            $additional_query .= " AND `player` != ?";
            $params[] = $hide;
        }

        if ($distinct) {
            $additional_query .= " AND `distinct` = 1";
        }

        return self::fetchIds($additional_query, $params, "player_conversations", "player");
    }

    /**
     * Get a list containing the IDs of each member team of the conversation
     *
     * @return int[] An array of team IDs
     */
    public function getTeamIds()
    {
        return self::fetchIds("WHERE `conversation` = ?", $this->id, "team_conversations", "team");
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
        ), 'last_activity');

        Database::getInstance()->startTransaction();
        foreach ($members as $member) {
            $conversation->addMember($member);
        }
        Database::getInstance()->finishTransaction();

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
     * @param  bool $distinct Whether to only return true if a player is
     *                        specifically a member of the conversation, not
     *                        just a member of one of the conversation's teams (ignored if $member is a Team)
     * @return bool True if the given object belongs in the conversation, false if they don't
     */
    public function isMember($member, $distinct = false)
    {
        $type = ($member instanceof Player) ? 'player' : 'team';

        if ($type === 'player' and $distinct) {
            $distinctQuery = 'AND `distinct` = 1';
        } else {
            $distinctQuery = '';
        }

        $result = $this->db->query(
            "SELECT 1 FROM `{$type}_conversations` WHERE `conversation` = ?
              AND `$type` = ? $distinctQuery",
            array($this->id, $member->getId()));

        return count($result) > 0;
    }

    /**
     * Add a member to the discussion
     *
     * @param  Player|Team $member   The member to add
     * @param  bool        $distinct Whether to add the member as a distinct
     *                               player (ignored for teams)
     * @return void
     */
    public function addMember($member, $distinct = true)
    {
        if ($member instanceof Player) {
            // Mark individual players as distinct by creating or updating the
            // entry on the table
            if ($distinct) {
                $query = "INSERT INTO `player_conversations` (`conversation`, `player`, `distinct`) VALUES (?, ?, 1)
                  ON DUPLICATE KEY UPDATE `distinct` = 1";
            } else {
                $query = "INSERT IGNORE INTO `player_conversations` (`conversation`, `player`, `distinct`, `read`) VALUES (?, ?, 0, 1)";
            }

            $this->db->execute($query, array($this->getId(), $member->getId()));
        } elseif ($member instanceof Team) {
            // Add the team to the team_conversations table...
            $this->db->execute(
                "INSERT IGNORE INTO `team_conversations` (`conversation`, `team`) VALUES (?, ?)",
                array($this->getId(), $member->getId())
            );

            // ...and each of its members in the player_conversations table as
            // non-distinct (unless they were already there)
            foreach ($member->getMembers() as $player) {
                $this->db->execute(
                    "INSERT IGNORE INTO `player_conversations` (`conversation`, `player`, `distinct`) VALUES (?, ?, 0)",
                    array($this->getId(), $player->getId())
                );
            }
        }
    }

    /**
     * Find out if a player belongs to any of the conversation's teams
     *
     * This does not take into account whether the player is a distinct member
     * of the conversation (i.e. they have been invited separately)
     *
     * @param  Player $member The player to check
     * @return bool
     */
    public function isTeamMember($member)
    {
        $query = $this->db->query(
            "SELECT COUNT(*) as c FROM players
                INNER JOIN teams ON teams.id = players.team
                INNER JOIN team_conversations ON team_conversations.team = teams.id
                WHERE team_conversations.conversation = ?
                AND players.id = ?
                LIMIT 1", array($this->getId(), $member->getId())
        );

        return $query[0]['c'] > 0;
    }

    /**
     * Remove a member from the discussion
     *
     * @param  Player|Team $member The member to remove
     * @return void
     */
    public function removeMember($member)
    {
        if ($member instanceof Player) {
            if ($this->isTeamMember($member) && $member->getTeam()->getLeader()->isSameAs($member)) {
                // The player is the leader of a team in the conversation, don't
                // remove them entirely
                $this->db->execute(
                    "UPDATE `player_conversations` SET `distinct` = 0 WHERE `conversation` = ? AND `player` = ?", array($this->getId(), $member->getId())
                );
            } else {
                $this->db->execute(
                    "DELETE FROM `player_conversations` WHERE `conversation` = ? AND `player` = ?", array($this->getId(), $member->getId())
                );
            }
        } else {
            $this->db->execute(
                "DELETE `player_conversations` FROM `player_conversations`
                LEFT JOIN `players` ON players.id = player_conversations.player
                WHERE player_conversations.conversation = ?
                AND players.team = ?
                AND player_conversations.distinct = 0", array($this->getId(), $member->getId())
            );

            $this->db->execute(
                "DELETE FROM `team_conversations`
                WHERE conversation = ?
                AND team = ?", array($this->getId(), $member->getId())
            );
        }
    }

    /**
     * Find out which members of the conversation should receive an e-mail after a new
     * message has been sent
     *
     * @param  int   $except The ID of a player who won't receive an e-mail
     *                       (e.g. message author)
     * @param  bool  $read   Whether to only send e-mails to players who have
     *                       read all the previous messages in the conversation
     * @return int[] A player ID list
     */
    public function getWaitingForEmailIDs($except, $read = true)
    {
        $readQuery = ($read) ? 'AND pg.read = 1' : '';

        return $this->fetchIds(
            "LEFT JOIN players ON pg.player = players.id
                WHERE pg.conversation = ?
                $readQuery
                AND pg.player != ?
                AND players.verified = 1
                AND players.receives != \"nothing\"",
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
                'status'        => 'status'
            ),
            'name' => 'subject',
        ));
    }
}
