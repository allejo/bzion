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
        $news = $this->result;

        $this->subject = $news['subject'];
        $this->content = $news['content'];
        $this->created = new DateTime($news['created']);
        $this->updated = new DateTime($news['updated']);
        $this->status = $news['status'];

    }

}
