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
    protected $team_id;

    /**
     * The time the invitation was sent
     * @var TimeDate
     */
    protected $sent;

    /**
     * The time the invitation will expire
     * @var TimeDate
     */
    protected $expiration;

    /**
     * The optional message sent to a player to join a team
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $status;

    /**
     * An array of valid statuses an Invitation can be in.
     *
     * @var int[]
     */
    protected static $validStatuses = [self::STATUS_PENDING, self::STATUS_ACCEPTED, self::STATUS_DENIED];

    const DELETED_COLUMN = 'is_deleted';

    const STATUS_PENDING = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DENIED = 2;

    const TABLE = 'invitations';

    /**
     * {@inheritdoc}
     */
    protected function assignResult($invitation)
    {
        $this->invited_player = $invitation['invited_player'];
        $this->sent_by    = $invitation['sent_by'];
        $this->team_id    = $invitation['team'];
        $this->sent       = TimeDate::fromMysql($invitation['sent']);
        $this->expiration = TimeDate::fromMysql($invitation['expiration']);
        $this->status     = self::castStatus($invitation['status']);
        $this->is_deleted = $invitation['is_deleted'];
    }

    /**
     * {@inheritdoc}
     */
    protected function assignLazyResult($invitation)
    {
        $this->message = $invitation['text'];
    }

    /**
     * Get the player receiving the invite
     *
     * @return Player
     */
    public function getInvitedPlayer()
    {
        return Player::get($this->invited_player);
    }

    /**
     * Get the sender of the invite
     *
     * @return Player
     */
    public function getSentBy()
    {
        return Player::get($this->sent_by);
    }

    /**
     * Get the team a player was invited to
     *
     * @return Team
     */
    public function getTeam()
    {
        return Team::get($this->team_id);
    }

    /**
     * Get the timestamp of when the invitation was sent.
     *
     * @return TimeDate
     */
    public function getSendTimestamp()
    {
        return $this->sent;
    }

    /**
     * Get the time when the invitation will expire
     *
     * @return TimeDate
     */
    public function getExpiration()
    {
        return $this->expiration->copy();
    }

    /**
     * Get the optional message sent to a player to join a team
     *
     * @return string
     */
    public function getMessage()
    {
        $this->lazyLoad();

        return $this->message;
    }

    /**
     * Get the current status of the Invitation.
     *
     * @see Invitation::STATUS_PENDING
     * @see Invitation::STATUS_ACCEPTED
     * @see Invitation::STATUS_DENIED
     *
     * @since 0.11.0
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Whether or not an invitation has expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expiration->lt(TimeDate::now());
    }

    /**
     * Mark the invitation as having expired
     *
     * @return self
     */
    public function setExpired()
    {
        return $this->updateProperty($this->expiration, 'expiration', TimeDate::now());
    }

    /**
     * Update the status for this Invitation
     *
     * @param int $statusValue
     *
     * @see Invitation::STATUS_PENDING
     * @see Invitation::STATUS_ACCEPTED
     * @see Invitation::STATUS_DENIED
     *
     * @since 0.11.0
     *
     * @throws InvalidArgumentException When an invalid status is given as an argument
     *
     * @return static
     */
    public function setStatus($statusValue)
    {
        if (!in_array($statusValue, self::$validStatuses)) {
            throw new InvalidArgumentException('Invalid value was used; see Invitation::$validStatuses for valid values.');
        }

        return $this->updateProperty($this->status, 'status', $statusValue);
    }

    /**
     * Send an invitation to join a team
     *
     * @param  int        $playerID The ID of the player who will receive the invitation
     * @param  int        $teamID   The team ID to which a player has been invited to
     * @param  int|null   $from     The ID of the player who sent it
     * @param  string     $message  (Optional) The message that will be displayed to the person receiving the invitation
     * @param  string|TimeDate|null $expiration The expiration time of the invitation (defaults to 1 week from now)
     *
     * @return Invitation The object of the invitation just sent
     */
    public static function sendInvite($playerID, $teamID, $from = null, $message = '', $expiration = null)
    {
        if ($expiration === null) {
            $expiration = TimeDate::now()->addWeek();
        } else {
            $expiration = Timedate::from($expiration);
        }

        $invitation = self::create([
            'invited_player' => $playerID,
            'sent_by'        => $from,
            'team'           => $teamID,
            'sent'           => TimeDate::now()->toMysql(),
            'expiration'     => $expiration->toMysql(),
            'message'        => $message,
            'status'         => self::STATUS_PENDING,
        ]);

        return $invitation;
    }

    /**
     * {@inheritdoc}
     */
    public static function getQueryBuilder()
    {
        return QueryBuilderFlex::createForModel(Invitation::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function getEagerColumnsList()
    {
        return [
            'id',
            'invited_player',
            'sent_by',
            'team',
            'sent',
            'expiration',
            'status',
            'is_deleted',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getLazyColumnsList()
    {
        return [
            'message',
        ];
    }

    /**
     * Find whether there are unexpired invitations for a player and a team
     *
     * @param  Player|int $player
     * @param  Team|int $team
     *
     * @return int
     */
    public static function playerHasInvitationToTeam($player, $team)
    {
        return (bool)self::getQueryBuilder()
            ->where('invited_player', '=', $player)
            ->where('team', '=', $team)
            ->where('expiration', '<', 'UTC_TIMESTAMP()')
            ->count()
        ;
    }

    /**
     * Cast a value to a valid Invitation status.
     *
     * @param int $status
     *
     * @see Invitation::STATUS_PENDING
     * @see Invitation::STATUS_ACCEPTED
     * @see Invitation::STATUS_DENIED
     *
     * @return int
     */
    protected static function castStatus($status)
    {
        if (in_array($status, self::$validStatuses)) {
            return $status;
        }

        return self::STATUS_PENDING;
    }
}
