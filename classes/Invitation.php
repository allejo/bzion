<?php

class Invitation
{
    /**
     * The ID of the invitation
     * @var int
     */
    private $id;

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
     * The database variable used for queries
     * @var Database
     */
    private $db;

    /**
     * Construct a new Team
     * @param int $id The team's id
     */
    function __construct($id)
    {
        $this->db = $GLOBALS['db'];
        $results = $this->db->query("SELECT * FROM invitations WHERE id = ?", "i", array($id));

        $this->id = $id;
        $this->invited_player = $results['invited_player'];
        $this->sent_by = $results['sent_by'];
        $this->team = $results['team'];
        $this->expiration = $results['expiration'];
        $this->text = $results['text'];
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
        $db = $GLOBALS['db'];
        $db->query("INSERT INTO invitations VALUES (NULL, ?, ?, ?, ADDDATE(NOW(), INTERVAL 7 DAY), ?)", "iis", array($to, $from, $teamid, $message));

        return new Invitation($db->getInsertId());
    }
}
