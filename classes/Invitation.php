<?php

class Invitation extends Controller
{

    /**
     * The BZID of the player receiving the invite
     * @var int
     */
    private $invited_player;

    /**
     * The BZID of the sender of the invite
     * @var int
     */
    private $sent_by;

    /**
     * The ID of the team a player was invited to
     * @var int
     */
    private $team;

    /**
     * The time the invitation will expire (Format: YYYY-MM-DD HH:MM:SS)
     * @var date
     */
    private $expiration;

    /**
     * The optional message sent to a player to join a team
     * @var string
     */
    private $text;

    /**
     * Construct a new invite
     * @param int $id The invite's id
     */
    function __construct($id)
    {
        parent::__construct($id, "invitations");
        $invitation = $this->result;

        $this->invited_player = $invitation['invited_player'];
        $this->sent_by = $invitation['sent_by'];
        $this->team = $invitation['team'];
        $this->expiration = $invitation['expiration'];
        $this->text = $invitation['text'];
    }

    /**
     * Send an invitation to join a team
     * @param int $to The BZID of the player who will receive the invitation
     * @param int $from The BZID of the player who sent it
     * @param int $teamid The team ID to which a player has been invited to
     * @param string $message (Optional) The message that will be displayed to the person receiving the invitation
     * @return Invitation The object of the invitation just sent
     */
    public static function sendInvite($to, $from, $teamid, $message = "")
    {
        $db = Database::getInstance();
        $db->query("INSERT INTO invitations VALUES (NULL, ?, ?, ?, ADDDATE(NOW(), INTERVAL 7 DAY), ?)", "iis", array($to, $from, $teamid, $message));

        return new Invitation($db->getInsertId());
    }
}
