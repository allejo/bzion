<?php

class Page extends Controller {

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
     * The bzid of the author of the page
     * @var int
     */
    private $author;

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
        $this->status = $page['status'];

    }

    function getName() {
        return $this->name;
    }

    function getContent() {
        return $this->content;
    }

    function getCreated() {
        return $this->created->diffForHumans();
    }

    function getUpdated() {
        return $this->updated->diffForHumans();
    }

    function getAuthor() {
        return $this->author;
    }

    function getStatus() {
        return $this->status;
    }

    /**
     * Get the URL that points to the page
     * @return string The page's URL, without a trailing slash
     */
    function getURL($dir="", $default=NULL) {
        return parent::getURL($dir, $default);
    }

    public static function getPages() {
        return parent::getIdsFrom("status", array("live"), "s");
    }

    /**
     * Gets a page object from the supplied alias
     * @param string $alias The page's alias
     * @return Page The page
     */
    public static function getFromAlias($alias) {
        return new Page(parent::getIdFrom($alias, "alias"));
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

}
