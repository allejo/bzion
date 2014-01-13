<?php

class News extends Controller {

    /**
     * The subject of the news article
     * @var string
     */
    private $subject;

    /**
     * The content of the news article
     * @var string
     */
    private $content;

    /**
     * The creation date of the news article
     * @var string
     */
    private $created;

    /**
     * The date the news article was last updated
     * @var string
     */
    private $updated;

    /**
     * The bzid of the author of the news article
     * @var int
     */
    private $author;

    /**
     * The status of the news article
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "news";

    /**
     * Construct a new News article
     * @param int $id The news article's id
     */
    function __construct($id) {

        parent::__construct($id);
        if (!$this->valid) return;

        $news = $this->result;

        $this->subject = $news['subject'];
        $this->content = $news['content'];
        $this->created = new TimeDate($news['created']);
        $this->updated = new TimeDate($news['updated']);
        $this->author = $news['author'];
        $this->status = $news['status'];

    }

    function getSubject() {
        return $this->subject;
    }

    function getAuthor() {
        return $this->author;
    }

    function getContent() {
        return $this->content;
    }

    function getUpdated() {
        return $this->updated->diffForHumans();
    }

    function getCreated() {
        return $this->created->diffForHumans();
    }

    /**
     * Get all the news entries in the database that aren't disabled or deleted
     * @param string $select The column to retrieve
     * @return array An array of news IDs
     */
    public static function getNews($select = "id") {
        return parent::getIdsFrom("status", array("disabled", "deleted"), "s", true, $select, "ORDER BY updated DESC");
    }

}
