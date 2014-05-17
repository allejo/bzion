<?php
/**
 * This file contains functionality relating to the teams belonging to the current league
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use \Identicon\Identicon;

/**
 * A league team
 */
class Team extends AliasModel
{

    /**
     * The name of the team
     *
     * @var string
     */
    private $name;

    /**
     * The description of the team written in markdown
     *
     * @var string
     */
    private $description_md;

    /**
     * The description of the team parsed to HTML
     *
     * @var string
     */
    private $description_html;

    /**
     * The url of the team's avatar
     *
     * @var string
     */
    private $avatar;

    /**
     * The creation date of the team
     *
     * @var TimeDate
     */
    private $created;

    /**
     * The team's current elo
     *
     * @var int
     */
    private $elo;

    /**
     * The team's activity
     *
     * @var double
     */
    private $activity;

    /**
     * The id of the team leader
     *
     * @var int
     */
    private $leader;

    /**
     * The number of matches won
     *
     * @var int
     */
    private $matches_won;

    /**
     * The number of matches lost
     *
     * @var int
     */
    private $matches_lost;

    /**
     * The number of matches tied
     *
     * @var int
     */
    private $matches_draw;

    /**
     * The total number of matches
     *
     * @var int
     */
    private $matches_total;

    /**
     * The number of members
     *
     * @var int
     */
    private $members;

    /**
     * The team's status
     *
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "teams";

    /**
     * The location of identicons will stored in
     */
    const IDENTICON_LOCATION = "/assets/imgs/identicons/teams/";

    /**
     * Construct a new Team
     *
     * @param int $id The team's id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        if (!$this->valid)
            return;

        $team = $this->result;

        $this->name = $team['name'];
        $this->alias = $team['alias'];
        $this->description_md = $team['description_md'];
        $this->description_html = $team['description_html'];
        $this->avatar = $team['avatar'];
        $this->created = new TimeDate($team['created']);
        $this->elo = $team['elo'];
        $this->activity = $team['activity'];
        $this->leader = $team['leader'];
        $this->matches_won = $team['matches_won'];
        $this->matches_lost = $team['matches_lost'];
        $this->matches_draw = $team['matches_draw'];
        $this->members = $team['members'];
        $this->status = $team['status'];

        $this->matches_total = $this->matches_won + $this->matches_lost + $this->matches_draw;
    }

    /**
     * Adds a new member to the team
     *
     * @param int $id The id of the player to add to the team
     */
    public function addMember($id)
    {
        $this->db->query("UPDATE players SET team=? WHERE id=?", "ii", array(
                $this->id,
                $id
            ));
        $this->update('members', ++$this->members, "i");
    }

    /**
     * Increase or decrease the ELO of the team
     *
     * @param int $adjust The value to be added to the current ELO (negative to substract)
     */
    public function changeElo($adjust)
    {
        $this->elo += $adjust;
        $this->update("elo", $this->elo, "i");
    }

    /**
     * Increment the team's match count
     *
     * @param int    $adjust The number to add to the current matches number (negative to substract)
     * @param string $type   The match count that should be changed. Can be 'win', 'draw' or 'loss'
     */
    public function changeMatchCount($adjust, $type)
    {
        $this->matches_total += $adjust;

        switch ($type) {
            case "win":
            case "won":
                $this->update("matches_won", $this->matches_won += $adjust, "i");

                return;
            case "loss":
            case "lost":
                $this->update("matches_lost", $this->matches_lost += $adjust, "i");

                return;
            default:
                $this->update("matches_draw", $this->matches_draw += $adjust, "i");

                return;
        }
    }

    /**
     * Decrement the team's match count by one
     *
     * @param string $type The type of the match. Can be 'win', 'draw' or 'loss'
     */
    public function decrementMatchCount($type)
    {
        $this->changeMatchCount(-1, $type);
    }

    /**
     * Get the activity of the team
     *
     * @return string The team's activity formated to two decimal places
     */
    public function getActivity()
    {
        return sprintf("%.2f", $this->activity);
    }

    /**
     * Get URL for the image used as the team avatar
     *
     * @return string The URL for the avatar
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Get the team's avatar as an HTML image element
     *
     * @return string The HTML for the image
     */
    public function getAvatarLiteral()
    {
        return '<img class="team_avatar" src="' . $this->getAvatar() . '">';
    }

    /**
     * Get the creation date of the team
     *
     * @return string The creation date of the team
     */
    public function getCreationDate()
    {
        return $this->created->diffForHumans();
    }

    /**
     * Get the description of the team
     *
     * @return string The description of the team
     */
    public function getDescription() {
        return $this->description_html;
    }

    /**
     * Get the current elo of the team
     *
     * @return int The elo of the team
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * Get the identicon for a player. This function will create one if it does not already exist
     *
     * @return string The URL to the generated identicon
     */
    public function getIdenticon()
    {
        $fileName = $this->getIdenticonPath();

        if (!$this->hasIdenticon())
        {
            $identicon = new Identicon();
            $imageDataUri = $identicon->getImageDataUri($this->getName(), 250);

            $file_handler = fopen($fileName, 'w');
            fwrite($file_handler, $imageDataUri);
            fclose($file_handler);
        }

        return Service::getRequest()->getBaseUrl() . $fileName;
    }

    /**
     * Get the leader of the team
     *
     * @return Player The object representing the team leader
     */
    public function getLeader()
    {
        return new Player($this->leader);
    }

    /**
     * Generate the HTML for a hyperlink to link to a team's profile
     * @return string The HTML hyperlink to a team's profile
     */
    public function getLinkLiteral()
    {
        return '<a href="' . $this->getURL() . '">' . $this->getName() . '</a>';
    }

    /**
     * Get the matches this team has participated in
     *
     * @param int $count  The offset used when fetching matches, i.e. the starting point
     * @param int $offset The amount of matches to be retrieved
     *
     * @return Match[] The array of match IDs this team has participated in
     */
    public function getMatches($count = 5, $offset = 0)
    {
        return Match::getMatchesByTeam($this->id, $offset, $count);
    }

    /**
     * Get the number of matches that resulted as a draw
     *
     * @return int The number of matches, respectively
     */
    public function getMatchesDraw()
    {
        return $this->matches_draw;
    }

    /**
     * Get the number of matches that the team has lost
     *
     * @return int The number of matches, respectively
     */
    public function getMatchesLost()
    {
        return $this->matches_lost;
    }

    /**
     * Get the URL that points to the team's list of matches
     *
     * @return string The team's list of matches
     */
    public function getMatchesURL()
    {
        return Service::getGenerator()->generate("match_by_team_list", array("team" => $this->getAlias()));
    }

    /**
     * Get the number of matches that the team has won
     *
     * @return int The number of matches, respectively
     */
    public function getMatchesWon()
    {
        return $this->matches_won;
    }

    /**
     * Get the members on the team
     *
     * @return Player[] The members on the team
     */
    public function getMembers()
    {
        return Player::getTeamMembers($this->id);
    }

    /**
     * Get the name of the team
     *
     * @return string The name of the team
     */
    public function getName()
    {
        if (!$this->valid)
            return "<em>None</em>";
        return $this->name;
    }

    /**
     * Get the number of members on the team
     *
     * @return int The number of members on the team
     */
    public function getNumMembers()
    {
        return $this->members;
    }

    /**
     * Get the total number of matches this team has played
     *
     * @return int The total number of matches this team has played
     */
    public function getNumTotalMatches()
    {
        return $this->matches_total;
    }

    /**
     * Get the rank category a team belongs too based on their ELO> This value is always a multiple of 100
     *
     * @return int The rank category a team belongs to
     */
    public function getRankValue()
    {
        return floor($this->getElo() / 100) * 100;
    }

    /**
     * Get the image associated with
     *
     * @return string
     */
    public function getRankImage()
    {
        return Service::getRequest()->getBaseUrl() . '/assets/imgs/ranks/' . $this->getRankValue() . '.png';
    }

    /**
     * Get the HTML for an image with the rank symbol
     *
     * @return string The HTML for a rank image
     */
    public function getRankImageLiteral()
    {
        return '<img class="rank_image" src="' . $this->getRankImage() . '" >';
    }

    /**
     * Check if the team has an identicon already made
     *
     * @return bool True if the identicon already exists
     */
    public function hasIdenticon()
    {
        return file_exists(self::IDENTICON_LOCATION . $this->getAlias());
    }

    /**
     * Increment the team's match count by one
     *
     * @param string $type The type of the match. Can be 'win', 'draw' or 'loss'
     */
    public function incrementMatchCount($type)
    {
        $this->changeMatchCount(1, $type);
    }

    /**
     * Removes a member from the team
     *
     * *Warning*: This method does not check whether the player is already
     * a member of the team
     *
     * @param int $id The id of the player to remove
     */
    public function removeMember($id)
    {
        $this->db->query("UPDATE players SET team=NULL WHERE id=?", "i", array(
            $id
        ));
        $this->update('members', --$this->members, "i");
    }

    /**
     * Update the description of the team
     *
     * @param string $description_md The description of the team written as markdown
     *
     * @return bool Whether or not both the HTML and MD entries in the database were updated
     */
    public function setDescription($description_md)
    {
        $mdUpdate = $this->update("description_md", $description_md, "s");
        $htmlUpdate = $this->update("description_html", parent::mdTransform($description_md), "s");

        return ($mdUpdate && $htmlUpdate);
    }

    /**
     * Get the path to the identicon
     *
     * @return string The path to the image
     */
    private function getIdenticonPath()
    {
        return DOC_ROOT . self::IDENTICON_LOCATION . $this->getAlias() . ".png";
    }

    /**
     * Create a new team
     *
     * @param  string $name        The name of the team
     * @param  int    $leader      The ID of the person creating the team, also the leader
     * @param  string $avatar      The URL to the team's avatar
     * @param  string $description The team's description
     * @return Team   An object that represents the newly created team
     */
    public static function createTeam($name, $leader, $avatar, $description)
    {
        $alias = self::generateAlias($name);

        $db = Database::getInstance();

        $query = "INSERT INTO teams VALUES(NULL, ?, ?, ?, ?, ?, NOW(), 1200, 0.00, ?, 0, 0, 0, 0, 'open')";
        $params = array(
            $name,
            $alias,
            $description,
            parent::mdTransform($description),
            $avatar,
            $leader
        );

        $db->query($query, "sssssi", $params);
        $id = $db->getInsertId();

        // If the generateAlias() method couldn't find an appropriate alias,
        // just make it the same as the ID
        if ($alias === null) {
            $db->query("UPDATE teams SET alias = id WHERE id = ?", 'i', array(
                    $id
                ));
        }

        $team = new Team($id);

        $team->addMember($leader);

        return $team;
    }

    /**
     * Get all the teams in the database that are not disabled or deleted
     *
     * @return Team[] An array of Team IDs
     */
    public static function getTeams()
    {
        return self::arrayIdToModel(
            parent::fetchIdsFrom(
                "status", array("disabled", "deleted"),
                "s", true, "ORDER BY elo DESC"
            )
        );
    }
}
