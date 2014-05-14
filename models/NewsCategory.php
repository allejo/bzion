<?php
/**
 * This file contains functionality relating to the news categories admins can use
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A news category
 */
class NewsCategory extends AliasModel
{
    /**
     * The slug used for the category
     * @var string
     */
    private $slug;

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
     * The status of the category: 'live' or 'deleted'
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

        $this->slug = $category['slug'];
        $this->name = $category['name'];
        $this->protected = $category['protected'];
        $this->status = $category['status'];
    }

    /**
     * Delete a category. Only delete a category if it is not protected
     */
    public function delete()
    {
        if ($this->isProtected())
        {
            parent::delete();
        }
    }

    /**
     * Get the slug of the category
     *
     * @return string The slug
     */
    public function getSlug()
    {
        return $this->slug;
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
     * @return string Either 'live' or 'deleted'
     */
    public function getStatus()
    {
        return $this->status;
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
            "INSERT INTO news_categories (id, slug, name, protected, status) VALUES (NULL, ?, ?, 0, 'live')",
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
                "ORDER BY name DESC"
            )
        );
    }
} 