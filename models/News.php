<?php
/**
 * This file contains functionality relating to the news articles admins can post
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A news article
 */
class News extends Model {

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
     * The ID of the author of the news article
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
    public function __construct($id) {

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

    /**
     * Get the subject of the news article
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Get the author of the news article
     * @return Player
     */
    public function getAuthor() {
        return new Player($this->author);
    }

    /**
     * Get the content of the article
     * @return string The raw content of the article
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Get the time when the article was last updated
     * @return string The article's last update time in a human-readable form
     */
    public function getUpdated() {
        return $this->updated->diffForHumans();
    }

    /**
     * Get the time when the article was submitted
     * @return string The article's creation time in a human-readable form
     */
    public function getCreated() {
        return $this->created->diffForHumans();
    }

    /**
     * Add a new news article
     *
     * @param string $subject The subject of the article
     * @param string $content The content of the article
     * @param int $authorID The ID of the author
     * @param string $status The status of the article: 'live', 'disabled', or 'deleted'
     *
     * @return News An object representing the article that was just posted
     */
    public static function addNews($subject, $content, $authorID, $status = 'live')
    {
        $db = Database::getInstance();

        $db->query(
            "INSERT INTO news (id, subject, content, created, updated, author, status) VALUES (NULL, ?, ?, NOW(), NOW(), ?, ?)",
            "ssis", array($subject, $content, $authorID, $status)
        );

        $article = new News($db->getInsertId());

        return $article;
    }

    /**
     * Get all the news entries in the database that aren't disabled or deleted
     *
     * @param int $start The offset used when fetching matches, i.e. the starting point
     * @param int $limit The amount of matches to be retrieved
     *
     * @return News[] An array of news objects
     */
    public static function getNews($start = 0, $limit = 5) {
        return self::arrayIdToModel(
            self::fetchIdsFrom(
                "status", array("disabled", "deleted"), "s", true,
                "ORDER BY created DESC LIMIT $limit OFFSET $start"
            )
        );
    }

}
