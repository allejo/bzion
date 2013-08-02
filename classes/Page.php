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
     * @var string
     */
    private $created;

    /**
     * The date the page was last updated
     * @var string
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
        $page = $this->result;

        $this->name = $page['subject'];
        $this->content = $page['content'];
        $this->created = new DateTime($page['created']);
        $this->updated = new DateTime($page['updated']);
        $this->author = $page['author'];
        $this->status = $page['page'];

    }

}
