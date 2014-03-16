<?php

/**
 * A custom page
 */
class Page extends AliasModel {

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
    function __construct($id) {

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
    function getName() {
        return $this->name;
    }

    /**
     * Get the raw content of the page
     * @return string
     */
    function getContent() {
        return $this->content;
    }

    /**
     * Get the page's submission time
     * @return string The time when the page was created in a human-readable format
     */
    function getCreated() {
        return $this->created->diffForHumans();
    }

    /**
     * Get the time when the page was last updated
     * @return string The page's last update time in a human-readable form
     */
    function getUpdated() {
        return $this->updated->diffForHumans();
    }

    /**
     * Get the user who created the page
     * @return Player The page's author
     */
    function getAuthor() {
        return new Player($this->author);
    }

    /**
     * Get the status of the page
     * @return string
     */
    function getStatus() {
        return $this->status;
    }

    /**
     * Find out whether this is the homepage
     * @return bool
     */
    function isHomePage() {
        return $this->home;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getRouteName() {
        return "custom_page";
    }

    /**
     * Get a list of enabled pages
     * @return Page[] A list of Page IDs
     */
    public static function getPages() {
        $pages = array();
        $pageIDs = parent::fetchIdsFrom("status", array("live"), "s");

        foreach ($pageIDs as $page)
        {
            $pages[] = new Page($page);
        }

        return $pages;
    }

     /**
     * Generate a URL-friendly unique alias for a page name
     *
     * @param string $name The original page name
     * @return string|Null The generated alias, or Null if we couldn't make one
     */
    static function generateAlias($name) {
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
    public static function getHomePage() {
        return new Page(parent::fetchIdFrom(1, "home"));
    }

}
