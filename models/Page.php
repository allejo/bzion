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
class Page extends AliasModel implements NamedModel, PermissionModel
{
    /**
     * The name of the page
     * @var string
     */
    protected $name;

    /**
     * The content of the page
     * @var string
     */
    protected $content;

    /**
     * The creation date of the page
     * @var TimeDate
     */
    protected $created;

    /**
     * The date the page was last updated
     * @var TimeDate
     */
    protected $updated;

    /**
     * The ID of the author of the page
     * @var int
     */
    protected $author;

    /**
     * Whether the page is the home page
     * @var boolean
     */
    protected $home;

    /**
     * The status of the page
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "pages";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($page)
    {
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
     * Set the name of the page
     *
     * @param  string $name
     * @return self
     */
    public function setName($name)
    {
        return $this->updateProperty($this->name, "name", $name, 's');
    }

    /**
     * Set the content of the page
     *
     * @param  string $content
     * @return self
     */
    public function setContent($content)
    {
        return $this->updateProperty($this->content, "content", $content, 's');
    }

    /**
     * Set the status of the page
     *
     * @param  string $status One of "live", "revision" or "disabled"
     * @return self
     */
    public function setStatus($status)
    {
        return $this->updateProperty($this->status, "status", $status, 's');
    }

    /**
     * Update the last edit timestamp
     * @return self
     */
    public function updateEditTimestamp()
    {
        return $this->updateProperty($this->updated, "updated", TimeDate::now(), 's');
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
        return self::create(array(
            'name' => $title,
            'alias' => self::generateAlias($title),
            'content' => $content,
            'author' => $authorID,
            'home' => 0,
            'status' => $status,
        ), 'sssiis', array('created', 'updated'));
    }

    /**
     * {@inheritdoc}
     */
    protected static function getRouteName($action='show')
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

    public static function getCreatePermission() { return Permission::CREATE_PAGE; }
    public static function getEditPermission() { return Permission::EDIT_PAGE;  }
    public static function getSoftDeletePermission() { return Permission::SOFT_DELETE_PAGE; }
    public static function getHardDeletePermission() { return Permission::HARD_DELETE_PAGE; }

}
