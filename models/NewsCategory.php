<?php
/**
 * This file contains functionality relating to the news categories admins can use
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * @TODO Create permissions for creating, editing, and modifying categories
 * @TODO Set up methods to modify the News Categories
 */

/**
 * A news category
 * @package    BZiON\Models
 */
class NewsCategory extends AliasModel
{
    /** @var bool Whether or not the category is protected from being deleted from the UI */
    protected $is_protected;

    /** @var bool When set to true, no new articles can be assigned this category */
    protected $is_read_only;

    const DEFAULT_STATUS = 'enabled';

    const DELETED_COLUMN = 'is_deleted';
    const TABLE = "news_categories";

    /**
     * {@inheritdoc}
     */
    protected function assignResult($category)
    {
        $this->alias = $category['alias'];
        $this->name = $category['name'];
        $this->is_protected = $category['is_protected'];
        $this->is_deleted = $category['is_deleted'];
    }

    /**
     * Delete a category. Only delete a category if it is not protected
     *
     * @throws DeletionDeniedException
     * @throws Exception
     */
    public function delete()
    {
        $hasArticles = (bool) News::getQueryBuilder()
            ->where('category', '=', $this->getId())
            ->active()
            ->count()
        ;

        if ($hasArticles) {
            throw new DeletionDeniedException('This category has news articles and cannot be deleted.');
        }

        if ($this->isProtected()) {
            throw new DeletionDeniedException('This category is protected and cannot be deleted.');
        }

        parent::delete();
    }

    /**
     * Get all the news entries in the category that aren't disabled or deleted
     *
     * @param int  $start     The offset used when fetching matches, i.e. the starting point
     * @param int  $limit     The amount of matches to be retrieved
     * @param bool $getDrafts Whether or not to fetch drafts
     *
     * @throws \Pixie\Exception
     * @throws Exception
     *
     * @return News[] An array of news objects
     */
    public function getNews($start = 0, $limit = 5, $getDrafts = false)
    {
        $qb = News::getQueryBuilder()
            ->limit($limit)
            ->offset($start)
            ->active()
            ->where('category', '=', $this->getId())
        ;

        if ($getDrafts) {
            $qb->whereNot('is_draft', '=', true);
        }

        return $qb->getModels(true);
    }

    /**
     * Check if the category is protected from being deleted.
     *
     * @return bool Whether or not the category is protected
     */
    public function isProtected()
    {
        return (bool) $this->is_protected;
    }

    /**
     * Check if new News article can be assigned this category.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return (bool) $this->is_read_only;
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
            'name'  => $name,
        ));
    }

    /**
     * Get all of the categories for the news
     *
     * @throws Exception
     *
     * @return NewsCategory[] An array of categories
     */
    public static function getCategories()
    {
        return self::getQueryBuilder()
            ->orderBy('name', 'ASC')
            ->active()
            ->getModels(true)
        ;
    }

    /**
     * Get a query builder for news categories.
     *
     * @throws Exception
     *
     * @return QueryBuilderFlex
     */
    public static function getQueryBuilder()
    {
        return QueryBuilderFlex::createForModel(NewsCategory::class)
            ->setNameColumn('name')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public static function getParamName()
    {
        return "category";
    }

    /**
     * {@inheritdoc}
     */
    public static function getTypeForHumans()
    {
        return "news category";
    }
}
