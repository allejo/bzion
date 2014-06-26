<?php
/**
 * This file contains a class to quickly generate database queries for models
 *
 * @package    BZiON\Models\QueryBuilder
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * This class can be used to search for models with specific characteristics in
 * the database.
 *
 * Note that most methods of this class return itself, so that you can easily
 * add a number of different filters.
 *
 * <code>
 *     return Player::getQueryBuilder()
 *     ->active()
 *     ->where('username')->startsWith('a')
 *     ->sortBy('username')->reverse()
 *     ->getModels();
 * </code>
 *
 * @package    BZiON\Models\QueryBuilder
 */
class QueryBuilder
{
    /**
     * The type of the model we're building a query for
     * @var string
     */
    private $type;

    /**
     * The columns that the model provided us
     * @var array
     */
    private $columns = array('id' => 'id');

    /**
     * The conditions to include in WHERE
     * @var string[]
     */
    private $conditions = array();

    /**
     * The MySQL value parameters
     * @var array
     */
    private $parameters = array();

    /**
     * The MySQL parameter types
     * @var string
     */
    private $types = '';

    /**
     * A column based on which we should sort the results
     * @var string|nulll
     */
    private $sortBy = null;

    /**
     * Whether to reverse the results
     * @var boolean
     */
    private $reverseSort = false;

    /**
     * The currently selected column
     * @var string|null
     */
    private $currentColumn = null;

    /**
     * Statuses to consider active
     * @var string[]|null
     */
    private $activeStatuses = null;

    /**
     * A column to consider the name of the model
     * @var string|null
     */
    private $nameColumn = null;

    /**
     * Whether to return the results as arrays instead of models
     * @var boolean
     */
    private $returnArray = false;

    /**
     * The page to return
     * @var int|null
     */
    private $page = null;

    /**
     * The number of elements on every page
     * @var int
     */
    private $resultsPerPage = 30;

    /**
     * Create a new QueryBuilder
     *
     * A new query builder should be created on a static getQueryBuilder()
     * method on each model. The options array can contain the following
     * properties:
     *
     * - `columns`: An associative array - the key of each entry is the column
     *   name that will be used by other methods, while the value is
     *   is the column name that is used in the database structure
     *
     * - `activeStatuses`: If the model has a status column, this should be
     *                     a list of values that make the entry be considered
     *                     "active"
     *
     * @param string $type    The type of the Model (e.g. "Player" or "Match")
     * @param array  $options The options to pass to the builder (see above)
     */
    public function __construct($type, $options=array())
    {
        $this->type = $type;

        if (isset($options['activeStatuses']))
            $this->activeStatuses = $options['activeStatuses'];

        if (isset($options['columns']))
            $this->columns += $options['columns'];

        if (isset($options['name']))
            $this->nameColumn = $options['name'];
    }

    /**
     * Select a column
     *
     * `$queryBuilder->where('username')->equals('administrator');`
     *
     * @param  string       $column The column to select
     * @return self
     */
    public function where($column)
    {
        if (!isset($this->columns[$column]))
            throw new Exception("Unknown column");

        $this->currentColumn = $this->columns[$column];

        return $this;
    }

    /**
     * Request that a column equals a string (case-insensitive)
     *
     * @param  string       $string The string that the column's value should equal to
     * @return self
     */
    public function equals($string)
    {
        $this->addColumnCondition("= ?", $string, 's');

        return $this;
    }

    /**
     * Request that a column equals a number
     *
     * @param  int|Model    $number The number that the column's value should equal
     *                              to - if a Model is provided, use the model's ID
     * @return self
     */
    public function is($number)
    {
        if ($number instanceof Model)
            $number = $number->getId();

        $this->addColumnCondition("= ?", $number, 'i');

        return $this;
    }

    /**
     * Request that a column value starts with a string (case-insensitive)
     *
     * @param  string       $string The substring that the column's value should start with
     * @return self
     */
    public function startsWith($string)
    {
        $this->addColumnCondition("LIKE CONCAT(?, '%')", $string, 's');

        return $this;
    }

    /**
     * Request that a specific model is not returned
     *
     * @param  Model        $model The model you don't want to get
     * @return self
     */
    public function except($model)
    {
        $this->where('id');
        $this->addColumnCondition("!= ?", $model->getId(), 'i');

        return $this;
    }

    /**
     * Return the results sorted by the value of a column
     *
     * @param  string       $column The column based on which the results should be ordered
     * @return self
     */
    public function sortBy($column)
    {
        if (!isset($this->columns[$column]))
            throw new Exception("Unknown column");

        $this->sortBy = $this->columns[$column];

        return $this;
    }

    /**
     * Reverse the order
     *
     * Note: This only works if you have specified a column in the sortBy() method
     *
     * @return self
     */
    public function reverse()
    {
        $this->reverseSort = !$this->reverseSort;

        return $this;
    }

    /**
     * Specify the number of results per page
     *
     * @param  int  $count The number of results
     * @return self
     */
    public function limit($count)
    {
        $this->resultsPerPage = $count;

        return $this;
    }

    /**
     * Only show results from a specific page
     *
     * @param  int|null $page The page number (or null to show all pages - counting starts from 0)
     * @return self
     */
    public function fromPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Request that only "active" Models should be returned
     *
     * @return self
     */
    public function active()
    {
        if ($this->activeStatuses === null)
            return $this;

        $statuses      = $this->activeStatuses;
        $statusCount   = count($statuses);
        $questionMarks = str_repeat(',?', $statusCount);
        $types         = str_repeat('s', $statusCount);

        // Remove first comma from questionMarks so that MySQL can read our query
        $questionMarks = ltrim($questionMarks, ',');

        $this->conditions[] = "`status` IN ($questionMarks)";
        $this->types .= $types;
        $this->parameters = array_merge($this->parameters, $statuses);

        return $this;
    }

    /**
     * Perform the query and get back the results in an array of names
     *
     * @return string[] An array of the type $id => $name
     */
    public function getNames()
    {
        if (!$this->nameColumn)
            throw new Exception("You haven't specified a name column");

        $db = Database::getInstance();
        $columns = array($this->nameColumn);

        $results = $db->query($this->createQuery($columns), $this->types, $this->parameters);

        $return = array();
        foreach ($results as $r)
            $return[$r['id']] = $r[$this->nameColumn];

        return $return;
    }

    /**
     * Perform the query and get back the results in a list of arrays
     *
     * @param string|string[] The column(s) that should be returned
     * @return array[]
     */
    public function getArray($columns)
    {
        if (!is_array($columns))
            $columns = array($columns);

        $db = Database::getInstance();

        return $db->query($this->createQuery($columns), $this->types, $this->parameters);
    }

    /**
     * Perform the query and get the results as Models
     *
     * @return array
     */
    public function getModels()
    {
        $type = $this->type;
        $db   = Database::getInstance();

        $results = $db->query($this->createQuery(), $this->types, $this->parameters);

        $return = array();
        foreach ($results as $r)
            $return[] = new $type($r['id']);

        return $return;
    }

    /**
     * Add a condition for the column
     * @param  string $condition The MySQL condition
     * @param  mixed  $value     A value to pass to MySQL
     * @param  string $type      The type of the value
     * @return void
     */
    private function addColumnCondition($condition, $value, $type)
    {
        if (!$this->currentColumn)
            throw new Exception("You haven't selected a column!");

        $this->conditions[] = "{$this->currentColumn} $condition";
        $this->parameters[] = $value;
        $this->types       .= $type;

        $this->currentColumn = null;
    }

    /**
     * Get a MySQL query string in the requested format
     * @param  string[] $columns The columns that should be included (without the ID)
     * @return string   The query
     */
    private function createQuery($columns = array())
    {
        $type       = $this->type;
        $table      = $type::TABLE;
        $columns    = $this->createQueryColumns($columns);
        $conditions = $this->createQueryConditions();
        $order      = $this->createQueryOrder();
        $pagination = $this->createQueryPagination();

        return "SELECT $columns FROM $table $conditions $order $pagination";
    }

    /**
     * Generate the columns for the query
     * @param  string[] $columns The columns that should be included (without the ID)
     * @return string
     */
    private function createQueryColumns($columns = array())
    {
        $columnStrings = array('id');

        foreach ($columns as $returnName) {
            $dbName = $this->columns[$returnName];
            $columnStrings[] = "`$dbName` as `$returnName`";
        }

        return implode(',', $columnStrings);
    }

    /**
     * Generates all the WHERE conditions for the query
     * @return string
     */
    private function createQueryConditions()
    {
        if ($this->conditions)
            return 'WHERE ' . implode(' AND ', $this->conditions);

        return '';
    }

    /**
     * Generates the sorting instructions for the query
     * @return string
     */
    private function createQueryOrder()
    {
        if ($this->sortBy) {
            $order = 'ORDER BY ' . $this->sortBy;
            if ($this->reverseSort)
                $order .= ' DESC';
        } else {
            $order = '';
        }

        return $order;
    }

    /**
     * Generates the pagination instructions for the query
     * @return string
     */
    private function createQueryPagination()
    {
        if (!$this->page) {
            return '';
        }

        $firstElement = ($this->page - 1) * $this->resultsPerPage;

        $this->parameters[] = $firstElement;
        $this->parameters[] = $this->resultsPerPage;
        $this->types       .= 'ii';

        return "LIMIT ?, ?";
    }

}
