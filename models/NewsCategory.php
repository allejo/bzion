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
class NewsCategory extends AliasModel implements NamedModel
{
    /**
     * The name of the category
     * @var string
     */
    protected $name;

    /**
     * Whether or not the category is protected from being deleted
     * @var bool
     */
    protected $protected;

    /**
     * The status of the category: 'enabled', 'disabled', or 'deleted'
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "news_categories";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($category)
    {
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
     * @return void
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
     * @return void
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
        return self::create(array(
            'alias' => self::generateAlias($name),
            'name' => $name,
            'protected' => 0,
            'status' => 'enabled'
        ), 'ssis');
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
     * Get a query builder for news categories
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new QueryBuilder('NewsCategory', array(
            'columns' => array(
                'name' => 'name',
            ),
            'activeStatuses' => array('enabled'),
            'name' => 'name',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getParamName()
    {
        return "category";
    }
}
