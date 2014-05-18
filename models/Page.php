<?php
/**
 * This file contains functionality relating to the custom pages that admins can great
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A custom page
 * @package    BZiON\Models
 */
class Page extends AliasModel
{
    /**
     * The name of the page
     * @var string
     */
    private $name;

    /**
     * The content of the page
     * @var string
     */
    private $content;

    /**
     * The creation date of the page
     * @var TimeDate
     */
    private $created;

    /**
     * The date the page was last updated
     * @var TimeDate
     */
    private $updated;

    /**
     * The ID of the author of the page
     * @var int
     */
    private $author;

    /**
     * Whether the page is the home page
     * @var boolean
     */
    private $home;

    /**
     * The status of the page
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "pages";

    /**
     * Construct a new Page
     * @param int $id The page's id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        if (!$this->valid) return;

        $page = $this->result;

        $this->name = $page['name'];
        $this->alias = $page['alias'];
        $this->content = $page['content'];
        $this->created = new TimeDate($page['created']);
        $this->updated = new TimeDate($page['updated']);
        $this->author = $page['author'];
        $this->home = $page['home'];
        $this->status = $page['status'];

    }

    /**
     * Get the title of the page
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the raw content of the page
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the page's submission time
     * @return string The time when the page was created in a human-readable format
     */
    public function getCreated()
    {
        return $this->created->diffForHumans();
    }

    /**
     * Get the time when the page was last updated
     * @return string The page's last update time in a human-readable form
     */
    public function getUpdated()
    {
        return $this->updated->diffForHumans();
    }

    /**
     * Get the user who created the page
     * @return Player The page's author
     */
    public function getAuthor()
    {
        return new Player($this->author);
    }

    /**
     * Get the status of the page
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Find out whether this is the homepage
     * @return bool
     */
    public function isHomePage()
    {
        return $this->home;
    }

    /**
     * Create a new Page
     *
     * @param string $title    The title of the page
     * @param string $content  The content of page
     * @param int    $authorID The ID of the author
     * @param string $status   Page status: 'live','disabled',or 'deleted'
     *
     * @return Page An object representing the page that was just created
     */
    public static function addPage($title, $content, $authorID, $status = "live")
    {
        $db = Database::getInstance();

        $db->query(
            "INSERT INTO pages (id, name, alias, content, created, updated, author, home, status) VALUES (NULL, ?, ?, ?, NOW(), NOW(), ?, 0, ?)",
            "sssis", array($title, parent::generateAlias($title), $content, $authorID, $status)
        );

        $page = new Page($db->getInsertId());

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getRouteName()
    {
        return "custom_page";
    }

    /**
     * Get a list of enabled pages
     * @return Page[] A list of Page IDs
     */
    public static function getPages()
    {
        return self::arrayIdToModel(
            parent::fetchIdsFrom("status", array("live"), "s")
        );
    }

     /**
     * Generate a URL-friendly unique alias for a page name
     *
     * @param  string      $name The original page name
     * @return string|Null The generated alias, or Null if we couldn't make one
     */
    public static function generateAlias($name)
    {
        $alias = parent::generateAlias($name);

        $disallowed_aliases = array("bans", "index", "login", "logout", "matches",
                                    "messages", "news", "notifications", "pages",
                                    "players", "servers", "teams");

        while (in_array($alias, $disallowed_aliases)) {
            $alias .= '-';
        }

        return $alias;
    }

    /**
     * Get the home page
     * @return Page
     */
    public static function getHomePage()
    {
        return new Page(parent::fetchIdFrom(1, "home"));
    }

}
