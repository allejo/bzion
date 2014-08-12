<?php
/**
 * This file contains functionality relating to the invitation of players to join teams
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * An invitation sent to a player asking them to join a team
 * @package    BZiON\Models
 */
class Invitation extends UrlModel
{

    /**
     * The ID of the player receiving the invite
     * @var int
     */
    protected $invited_player;

    /**
     * The ID of the sender of the invite
     * @var int
     */
    protected $sent_by;

    /**
     * The ID of the team a player was invited to
     * @var int
     */
    protected $team;

    /**
     * The time the invitation will expire
     * @var TimeDate
     */
    protected $expiration;

    /**
     * The optional message sent to a player to join a team
     * @var string
     */
    protected $text;

    /**
     * The name of the database table used for queries
     */

    const TABLE = "invitations";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($invitation)
    {
        $this->invited_player = $invitation['invited_player'];
        $this->sent_by    = $invitation['sent_by'];
        $this->team       = $invitation['team'];
        $this->expiration = new TimeDate($invitation['expiration']);
        $this->text       = $invitation['text'];
    }

    /**
     * Send an invitation to join a team
     * @param  int        $to      The ID of the player who will receive the invitation
     * @param  int        $from    The ID of the player who sent it
     * @param  int        $teamid  The team ID to which a player has been invited to
     * @param  string     $message (Optional) The message that will be displayed to the person receiving the invitation
     * @return Invitation The object of the invitation just sent
     */
    public static function sendInvite($to, $from, $teamid, $message = "")
    {
        $invitation = self::create(array(
            "invited_player"  => $to,
            "sent_by"         => $from,
            "team"            => $teamid,
            "text"            => $message,
            "expiration"      => TimeDate::now()->addWeek()->toMysql(),
        ), 'iiiss');

        return $invitation;
    }

    /**
     * Get the player receiving the invite
     *
     * @return Player
     */
    public function getInvitedPlayer()
    {
        return new Player($this->invited_player);
    }

    /**
     * Get the sender of the invite
     *
     * @return Player
     */
    public function getSentBy()
    {
        return new Player($this->sent_by);
    }

    /**
     * Get the team a player was invited to
     *
     * @return Team
     */
    public function getTeam()
    {
        return new Team($this->team);
    }

    /**
     * Get the time when the invitation will expire
     *
     * @return TimeDate
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Mark the invitation as having expired
     *
     * @return self
     */
    public function updateExpiration()
    {
        return $this->updateProperty($this->expiration, 'expiration', TimeDate::now(), 's');
    }

    /**
     * Get the optional message sent to a player to join a team
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Find whether there are unexpired invitations for a player and a team
     *
     * @return boolean
     */
    public static function hasOpenInvitation($player, $team)
    {
        return self::fetchCount(
            "WHERE invited_player = ? AND team = ? AND expiration > NOW()",
            'ii', array($player, $team)
        );
    }
}
