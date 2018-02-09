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

    protected $is_draft;
    protected $is_unlisted;

    const DEFAULT_STATUS = 'live';

    const DELETED_COLUMN = 'is_deleted';
    const TABLE = 'pages';

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
        $this->is_unlisted = $page['is_unlisted'];
        $this->is_draft = $page['is_draft'];
        $this->is_deleted = $page['is_deleted'];
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
     * Get whether or not this Page is a draft.
     *
     * @since 0.11.0
     *
     * @return bool
     */
    public function isDraft()
    {
        return (bool)$this->is_draft;
    }

    /**
     * Get whether or not this Page was unlisted.
     *
     * An unlisted page will not appear in the secondary navigation.
     *
     * @since 0.11.0
     *
     * @return bool
     */
    public function isUnlisted()
    {
        return (bool)$this->is_unlisted;
    }

    /**
     * Set the content of the page
     *
     * @param  string $content
     *
     * @return static
     */
    public function setContent($content)
    {
        return $this->updateProperty($this->content, "content", $content);
    }

    /**
     * Set the draft status for this page.
     *
     * @param bool $draft
     *
     * @return static
     */
    public function setDraft($draft)
    {
        return $this->updateProperty($this->is_draft, 'is_draft', $draft);
    }

    /**
     * Set the unlisted status for this page.
     *
     * @param bool $unlisted
     *
     * @return static
     */
    public function setUnlisted($unlisted)
    {
        return $this->updateProperty($this->is_unlisted, 'is_unlisted', $unlisted);
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
     * @param string $title       The title of the page
     * @param string $content     The content of page
     * @param int    $authorID    The ID of the author
     * @param bool   $is_draft    Whether or not the page should be saved as a draft
     * @param bool   $is_unlisted Whether or not the page should be unlisted
     *
     * @since 0.11.0 The former enum $status parameter has been changed to the boolean $is_draft. The $is_unlisted
     *               argument has been added.
     *
     * @return Page An object representing the page that was just created
     */
    public static function addPage($title, $content, $authorID, $is_draft = false, $is_unlisted = false)
    {
        return self::create([
            'name'    => $title,
            'alias'   => self::generateAlias($title),
            'content' => $content,
            'author'  => $authorID,
            'is_draft' => (bool)$is_draft,
            'is_unlisted' => (bool)$is_unlisted,
        ], ['created', 'updated']);
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
     * {@inheritdoc}
     */
    public static function getQueryBuilder()
    {
        return QueryBuilderFlex::createForModel(Page::class)
            ->setNameColumn('name')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public static function getActiveModels(QueryBuilderFlex &$qb)
    {
        $qb
            ->whereNot(self::DELETED_COLUMN, '=', self::DELETED_VALUE)
            ->whereNot('is_draft', '=', true)
        ;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getEagerColumnsList()
    {
        return [
            'id',
            'name',
            'alias',
            'author',
            'is_draft',
            'is_deleted',
            'is_unlisted',
        ];
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
