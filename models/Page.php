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
     * @var bool
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

    const CREATE_PERMISSION = Permission::CREATE_PAGE;
    const EDIT_PERMISSION = Permission::EDIT_PAGE;
    const SOFT_DELETE_PERMISSION = Permission::SOFT_DELETE_PAGE;
    const HARD_DELETE_PERMISSION = Permission::HARD_DELETE_PAGE;

    /**
     * {@inheritdoc}
     */
    protected function assignResult($page)
    {
        $this->name = $page['name'];
        $this->alias = $page['alias'];
        $this->author = $page['author'];
        $this->home = $page['home'];
        $this->status = $page['status'];
    }

    /**
     * {@inheritdoc}
     */
    protected function assignLazyResult($page)
    {
        $this->content = $page['content'];
        $this->created = TimeDate::fromMysql($page['created']);
        $this->updated = TimeDate::fromMysql($page['updated']);
    }

    /**
     * Get the raw content of the page
     * @return string
     */
    public function getContent()
    {
        $this->lazyLoad();

        return $this->content;
    }

    /**
     * Get the page's submission time
     * @return TimeDate
     */
    public function getCreated()
    {
        $this->lazyLoad();

        return $this->created->copy();
    }

    /**
     * Get the time when the page was last updated
     * @return TimeDate
     */
    public function getUpdated()
    {
        $this->lazyLoad();

        return $this->updated->copy();
    }

    /**
     * Get the user who created the page
     * @return Player The page's author
     */
    public function getAuthor()
    {
        return Player::get($this->author);
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
     * Set the content of the page
     *
     * @param  string $content
     * @return self
     */
    public function setContent($content)
    {
        return $this->updateProperty($this->content, "content", $content);
    }

    /**
     * Set the status of the page
     *
     * @param  string $status One of "live", "revision" or "disabled"
     * @return self
     */
    public function setStatus($status)
    {
        return $this->updateProperty($this->status, "status", $status);
    }

    /**
     * Update the last edit timestamp
     * @return self
     */
    public function updateEditTimestamp()
    {
        return $this->updateProperty($this->updated, "updated", TimeDate::now());
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
            'name'    => $title,
            'alias'   => self::generateAlias($title),
            'content' => $content,
            'author'  => $authorID,
            'home'    => 0,
            'status'  => $status,
        ), array('created', 'updated'));
    }

    /**
     * {@inheritdoc}
     */
    public static function getRouteName($action = 'show')
    {
        return "custom_page_$action";
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDisallowedAliases()
    {
        return array(
            "admin", "bans", "index", "login", "logout", "maps", "matches",
            "messages", "news", "notifications", "pages", "players", "servers",
            "teams", "visits"
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getActiveStatuses()
    {
        return array('live', 'revision');
    }

    /**
     * {@inheritdoc}
     */
    public static function getEagerColumns($prefix = null)
    {
        $columns = [
            'id',
            'parent_id',
            'name',
            'alias',
            'author',
            'home',
            'status',
        ];

        return self::formatColumns($prefix, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public static function getLazyColumns()
    {
        return 'content,created,updated';
    }

    /**
     * Get a query builder for pages
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new QueryBuilder('Page', array(
            'columns' => array(
                'name'   => 'name',
                'status' => 'status'
            ),
            'name' => 'name'
        ));
    }

    /**
     * Get the home page
     * @deprecated
     * @return Page
     */
    public static function getHomePage()
    {
        return self::get(self::fetchIdFrom(1, "home"));
    }
}
