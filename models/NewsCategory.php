<?php
/**
 * This file contains functionality relating to the news categories admins can use
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A news category
 * @package    BZiON\Models
 */
class NewsCategory extends AliasModel
{
    /**
     * The name of the category
     * @var string
     */
    private $name;

    /**
     * Whether or not the category is protected from being deleted
     * @var bool
     */
    private $protected;

    /**
     * The status of the category: 'enabled', 'disabled', or 'deleted'
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "news_categories";

    /**
     * Construct a new News article
     * @param int $id The news article's id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        if (!$this->valid) return;

        $category = $this->result;

        $this->alias = $category['alias'];
        $this->name = $category['name'];
        $this->protected = $category['protected'];
        $this->status = $category['status'];
    }

    /**
     * Delete a category. Only delete a category if it is not protected
     */
    public function delete()
    {
        // Get any articles using this category
        $articles = News::fetchIdsFrom("category", $this->getId(), 'i');

        // Only delete a category if it is not protected and is not being used
        if (!$this->isProtected() && count($articles) == 0) {
            parent::delete();
        }
    }

    /**
     * Disable the category
     *
     * @return bool Will only return false if there was error when updating the database
     */
    public function disableCategory()
    {
        if ($this->getStatus() != "disabled") {
            $this->update("status", "disabled", 's');
        }
    }

    /**
     * Enable the category
     *
     * @return bool Will only return false if there was error when updating the database
     */
    public function enableCategory()
    {
        if ($this->getStatus() != "enabled") {
            $this->update("status", "enabled", 's');
        }
    }

    /**
     * Get the name of the category
     *
     * @return string The name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the status of the category
     *
     * @return string Either 'enabled', 'disabled', or 'deleted'
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Generate the HTML for a hyperlink to link to a categorys's page
     * @return string The HTML hyperlink to the category
     */
    public function getLinkLiteral()
    {
        return '<a href="' . $this->getURL() . '">' . $this->getName() . '</a>';
    }

    /**
     * Get all the news entries in the category that aren't disabled or deleted
     *
     * @param int  $start     The offset used when fetching matches, i.e. the starting point
     * @param int  $limit     The amount of matches to be retrieved
     * @param bool $getDrafts Whether or not to fetch drafts
     *
     * @return News[] An array of news objects
     */
    public function getNews($start = 0, $limit = 5, $getDrafts = false)
    {
        $ignoredStatuses = "";

        if (!$getDrafts) {
            $ignoredStatuses = "'draft', ";
        }

        $ignoredStatuses .= "'deleted'";

        $query  = "WHERE status NOT IN ($ignoredStatuses) AND category = ? ";
        $query .= "ORDER BY created DESC LIMIT $limit OFFSET $start";

        return News::arrayIdToModel(News::fetchIds($query, 'i', array($this->getId())));
    }

    /**
     * Check if the category is protected from being deleted
     *
     * @return bool Whether or not the category is protected
     */
    public function isProtected()
    {
        return $this->protected;
    }

    /**
     * Create a new category
     *
     * @param string $name The name of the category
     *
     * @return NewsCategory An object representing the category that was just created
     */
    public static function addCategory($name)
    {
        $db = Database::getInstance();

        $db->query(
            "INSERT INTO news_categories (id, alias, name, protected, status) VALUES (NULL, ?, ?, 0, 'enabled')",
            "ss", array(parent::generateAlias($name), $name)
        );

        $newsCategory = new NewsCategory($db->getInsertId());

        return $newsCategory;
    }

    /**
     * Get all of the categories for the news
     *
     * @return NewsCategory[] An array of categories
     */
    public static function getCategories()
    {
        return self::arrayIdToModel(
            self::fetchIdsFrom(
                "status", array("deleted"), "s", true,
                "ORDER BY name ASC"
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getParamName()
    {
        return "category";
    }
}
