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
 *     return Team::getQueryBuilder()
 *     ->active()
 *     ->where('name')->startsWith('a')
 *     ->sortBy('name')->reverse()
 *     ->getModels();
 * </code>
 *
 * @package    BZiON\Models\QueryBuilder
 */
class QueryBuilder implements Countable
{
    /**
     * The type of the model we're building a query for
     * @var string
     */
    protected $type;

    /**
     * The columns that the model provided us
     * @var array
     */
    protected $columns = array('id' => 'id');

    /**
     * The conditions to include in WHERE
     * @var string[]
     */
    protected $conditions = array();

    /**
     * The MySQL value parameters
     * @var array
     */
    protected $parameters = array();

    /**
     * The MySQL parameter types
     * @var string
     */
    protected $types = '';

    /**
     * The MySQL value parameters for pagination
     * @var array
     */
    protected $paginationParameters = array();

    /**
     * The MySQL parameter types for pagination
     * @var string
     */
    protected $paginationTypes = '';

    /**
     * Extra MySQL query string to pass
     * @var string
     */
    protected $extras = '';

    /**
     * A column based on which we should sort the results
     * @var string|null
     */
    private $sortBy = null;

    /**
     * Whether to reverse the results
     * @var bool
     */
    private $reverseSort = false;

    /**
     * The currently selected column
     * @var string|null
     */
    private $currentColumn = null;

    /**
     * The currently selected column without the table name (unless it was
     * explicitly provided)
     * @var string|null
     */
    protected $currentColumnRaw = null;

    /**
     * A column to consider the name of the model
     * @var string|null
     */
    private $nameColumn = null;

    /**
     * Whether to return the results as arrays instead of models
     * @var bool
     */
    private $returnArray = false;

    /**
     * The page to return
     * @var int|null
     */
    private $page = null;

    /**
     * Whether the ID of the first/last element has been provided
     * @var bool
     */
    private $limited = false;

    /**
     * The number of elements on every page
     * @var int
     */
    protected $resultsPerPage = 30;

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
     * - `name`: The name of the column which represents the name of the object
     *
     * @param string $type    The type of the Model (e.g. "Player" or "Match")
     * @param array  $options The options to pass to the builder (see above)
     */
    public function __construct($type, $options = array())
    {
        $this->type = $type;

        if (isset($options['columns'])) {
            $this->columns += $options['columns'];
        }

        if (isset($options['name'])) {
            $this->nameColumn = $options['name'];
        }
    }

    /**
     * Select a column
     *
     * `$queryBuilder->where('username')->equals('administrator');`
     *
     * @param  string $column The column to select
     * @return self
     */
    public function where($column)
    {
        if (!isset($this->columns[$column])) {
            throw new InvalidArgumentException("Unknown column '$column'");
        }

        $this->column($this->columns[$column]);

        return $this;
    }

    /**
     * Request that a column equals a string (case-insensitive)
     *
     * @param  string $string The string that the column's value should equal to
     * @return self
     */
    public function equals($string)
    {
        $this->addColumnCondition("= ?", $string, 's');

        return $this;
    }

    /**
     * Request that a column doesNOT equals a string (case-insensitive)
     *
     * @param  string $string The string that the column's value should equal to
     * @return self
     */
    public function notEquals($string)
    {
        $this->addColumnCondition("!= ?", $string, 's');

        return $this;
    }

    /**
     * Request that a timestamp is before the specified time
     *
     * @param string|TimeDate $time      The timestamp to compare to
     * @param bool            $inclusive Whether to include the given timestamp
     * @param bool            $reverse   Whether to reverse the results
     */
    public function isBefore($time, $inclusive = false, $reverse = false)
    {
        return $this->isAfter($time, $inclusive, !$reverse);
    }

    /**
     * Request that a timestamp is after the specified time
     *
     * @param string|TimeDate $time      The timestamp to compare to
     * @param bool            $inclusive Whether to include the given timestamp
     * @param bool            $reverse   Whether to reverse the results
     */
    public function isAfter($time, $inclusive = false, $reverse = false)
    {
        if ($time instanceof TimeDate) {
            $time = $time->toMysql();
        }

        $comparison  = ($reverse)   ? '<' : '>';
        $comparison .= ($inclusive) ? '=' : '';

        $this->addColumnCondition("$comparison ?",  $time, 's');

        return $this;
    }

    /**
     * Request that a column equals a number
     *
     * @param  int|Model|null $number The number that the column's value should
     *                                equal to. If a Model is provided, use the
     *                                model's ID, while null values are ignored.
     * @return self
     */
    public function is($number)
    {
        if ($number === null) {
            return $this;
        }

        if ($number instanceof Model) {
            $number = $number->getId();
        }

        $this->addColumnCondition("= ?", $number, 'i');

        return $this;
    }

    /**
     * Request that a column equals one of some strings
     *
     * @param  string[] $strings The list of accepted values for the column
     * @return self
     */
    public function isOneOf($strings)
    {
        $count = count($strings);
        $types = str_repeat('s', $count);
        $questionMarks = str_repeat(',?', $count);

        // Remove first comma from questionMarks so that MySQL can read our query
        $questionMarks = ltrim($questionMarks, ',');

        $this->addColumnCondition("IN ($questionMarks)", $strings, $types);

        return $this;
    }

    /**
     * Request that a column value starts with a string (case-insensitive)
     *
     * @param  string $string The substring that the column's value should start with
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
     * @param  Model|int $model The ID or model you don't want to get
     * @return self
     */
    public function except($model)
    {
        if ($model instanceof Model) {
            $model = $model->getId();
        }

        $this->where('id');
        $this->addColumnCondition("!= ?", $model, 'i');

        return $this;
    }

    /**
     * Return the results sorted by the value of a column
     *
     * @param  string $column The column based on which the results should be ordered
     * @return self
     */
    public function sortBy($column)
    {
        if (!isset($this->columns[$column])) {
            throw new Exception("Unknown column");
        }

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
     * End with a specific result
     *
     * @param  int|Model $model     The model (or database ID) after the first result
     * @param  bool   $inclusive Whether to include the provided model
     * @param  bool   $reverse   Whether to reverse the results
     * @return self
     */
    public function endAt($model, $inclusive = false, $reverse = false)
    {
        return $this->startAt($model, $inclusive, !$reverse);
    }

    /**
     * Start with a specific result
     *
     * @param  int|Model $model     The model (or database ID) before the first result
     * @param  bool   $inclusive Whether to include the provided model
     * @param  bool   $reverse   Whether to reverse the results
     * @return self
     */
    public function startAt($model, $inclusive = false, $reverse = false)
    {
        if (!$model) {
            return $this;
        } elseif ($model instanceof Model && !$model->isValid()) {
            return $this;
        }

        $this->column($this->sortBy);
        $this->limited = true;
        $column = $this->currentColumn;
        $table  = $this->getTable();

        $comparison  = $this->reverseSort ^ $reverse;
        $comparison  = ($comparison) ? '>' : '<';
        $comparison .= ($inclusive)  ? '=' : '';
        $id = ($model instanceof Model) ? $model->getId() : $model;

        // Compare an element's timestamp to the timestamp of $model; if it's the
        // same, perform the comparison using IDs
        $this->addColumnCondition(
            "$comparison (SELECT $column FROM $table WHERE id = ?) OR ($column = (SELECT $column FROM $table WHERE id = ?) AND id $comparison ?)",
            array($id, $id, $id),
            'iii'
        );

        return $this;
    }

    /**
     * Request that only "active" Models should be returned
     *
     * @return self
     */
    public function active()
    {
        if (!isset($this->columns['status'])) {
            return $this;
        }

        $type = $this->type;

        return $this->where('status')->isOneOf($type::getActiveStatuses());
    }

    /**
     * Make sure that Models invisible to a player are not returned
     *
     * Note that this method does not take PermissionModel::canBeSeenBy() into
     * consideration for performance purposes, so you will have to override this
     * in your query builder if necessary.
     *
     * @param  Player  $player      The player in question
     * @param  bool $showDeleted false to hide deleted models even from admins
     * @return self
     */
    public function visibleTo($player, $showDeleted = false)
    {
        $type = $this->type;

        if (is_subclass_of($type, "PermissionModel")
         && $player->hasPermission($type::EDIT_PERMISSION)) {
            // The player is an admin who can see hidden models
            if ($showDeleted) {
                return $this;
            } else {
                return $this->where('status')->notEquals('deleted');
            }
        } else {
            return $this->active();
        }
    }

    /**
     * Perform the query and get back the results in an array of names
     *
     * @return string[] An array of the type $id => $name
     */
    public function getNames()
    {
        if (!$this->nameColumn) {
            throw new Exception("You haven't specified a name column");
        }

        $results = $this->getArray($this->nameColumn);

        return array_column($results, $this->nameColumn, 'id');
    }

    /**
     * Perform the query and get back the results in a list of arrays
     *
     * @param string|string[] The column(s) that should be returned
     * @param  string  $columns
     * @return array[]
     */
    public function getArray($columns)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }

        $db = Database::getInstance();

        return $db->query($this->createQuery($columns), $this->getTypes(), $this->getParameters());
    }

    /**
     * Perform the query and get the results as Models
     *
     * @return array
     */
    public function getModels()
    {
        $db   = Database::getInstance();
        $type = $this->type;

        $results = $db->query($this->createQuery(), $this->getTypes(), $this->getParameters());

        return $type::arrayIdToModel(array_column($results, 'id'));
    }

    /**
     * Count the results
     *
     * @return int
     */
    public function count()
    {
        $table  = $this->getTable();
        $params = $this->createQueryParams(false);
        $db     = Database::getInstance();
        $query  = "SELECT COUNT(*) FROM $table $params";

        // We don't want pagination to affect our results so don't use the functions that combine
        // pagination results
        $results = $db->query($query, $this->types, $this->parameters);

        return $results[0]['COUNT(*)'];
    }

    /**
     * Count the number of pages that all the models could be separated into
     */
    public function countPages()
    {
        return ceil($this->count() / $this->getResultsPerPage());
    }

    /**
     * Find if there is any result
     *
     * @return bool
     */
    public function any()
    {
        // Make sure that we don't mess with the user's options
        $query = clone $this;

        $query->limit(1);

        return $query->count() > 0;
    }

    /**
     * Get the amount of results that are returned per page
     * @return int
     */
    public function getResultsPerPage()
    {
        return $this->resultsPerPage;
    }

    /**
     * Select a column to perform opeations on
     *
     * This is identical to the `where()` method, except that the column is
     * specified as a MySQL column and not as a column name given by the model
     *
     * @param  string $column The column to select
     * @return self
     */
    protected function column($column)
    {
        if (strpos($column, '.') === false) {
            // Add the table name to the column if it isn't there already so that
            // MySQL knows what to do when handling multiple tables
            $table = $this->getTable();
            $this->currentColumn = "`$table`.`$column`";
        } else {
            $this->currentColumn = $column;
        }

        $this->currentColumnRaw = $column;

        return $this;
    }

    /**
     * Add a condition for the column
     * @param  string $condition The MySQL condition
     * @param  mixed  $value     Value(s) to pass to MySQL
     * @param  string $type      The type of the values
     * @return void
     */
    protected function addColumnCondition($condition, $value, $type)
    {
        if (!$this->currentColumn) {
            throw new Exception("You haven't selected a column!");
        }

        if (!is_array($value)) {
            $value = array($value);
        }

        $this->conditions[] = "{$this->currentColumn} $condition";
        $this->parameters   = array_merge($this->parameters, $value);
        $this->types       .= $type;

        $this->currentColumn = null;
        $this->currentColumnRaw = null;
    }

    /**
     * Get the MySQL extra parameters
     *
     * @param  bool $respectPagination Whether to respect pagination or not; useful for when pagination should be ignored such as count
     * @return string
     */
    protected function createQueryParams($respectPagination = true)
    {
        $extras     = $this->extras;
        $conditions = $this->createQueryConditions();
        $order      = $this->createQueryOrder();
        $pagination = "";

        if ($respectPagination) {
            $pagination = $this->createQueryPagination();
        }

        return "$extras $conditions $order $pagination";
    }

    /**
     * Get the query parameters
     *
     * @return array
     */
    protected function getParameters()
    {
        return array_merge($this->parameters, $this->paginationParameters);
    }

    /**
     * Get the query types
     *
     * @return string
     */
    protected function getTypes()
    {
        return $this->types . $this->paginationTypes;
    }

    /**
     * Get the table of the model
     *
     * @return string
     */
    protected function getTable()
    {
        $type = $this->type;

        return $type::TABLE;
    }

    /**
     * Get a MySQL query string in the requested format
     * @param  string[] $columns The columns that should be included (without the ID)
     * @return string   The query
     */
    protected function createQuery($columns = array())
    {
        $type     = $this->type;
        $table    = $type::TABLE;
        $columns  = $this->createQueryColumns($columns);
        $params   = $this->createQueryParams();

        return "SELECT $columns FROM $table $params";
    }

    /**
     * Generate the columns for the query
     * @param  string[] $columns The columns that should be included (without the ID)
     * @return string
     */
    private function createQueryColumns($columns = array())
    {
        $type = $this->type;
        $table = $type::TABLE;
        $columnStrings = array("`$table`.id");

        foreach ($columns as $returnName) {
            if (strpos($returnName, ' ') === false) {
                $dbName = $this->columns[$returnName];
                $columnStrings[] = "`$table`.`$dbName` as `$returnName`";
            } else {
                // "Column" contains a space, pass it as is
                $columnStrings[] = $returnName;
            }
        }

        return implode(',', $columnStrings);
    }

    /**
     * Generates all the WHERE conditions for the query
     * @return string
     */
    private function createQueryConditions()
    {
        if ($this->conditions) {
            // Add parentheses around the conditions to prevent conflicts due
            // to the order of operations
            $conditions = array_map(function ($value) { return "($value)"; }, $this->conditions);

            return 'WHERE ' . implode(' AND ', $conditions);
        }

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

            // Sort by ID if the sorting columns are equal
            $id = '`' . $this->getTable() . '`.`id`';
            if ($this->reverseSort) {
                $order .= " DESC, $id DESC";
            } else {
                $order .= ", $id";
            }
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
        // Reset mysqli params and types just in case createQueryParagination()
        // had been called earlier
        $this->paginationParameters = array();
        $this->paginationTypes = "";

        if (!$this->page && !$this->limited) {
            return '';
        }

        $offset = '';
        if ($this->page) {
            $firstElement = ($this->page - 1) * $this->resultsPerPage;
            $this->paginationParameters[] = $firstElement;
            $this->paginationTypes       .= 'i';

            $offset = '?,';
        }

        $this->paginationParameters[] = $this->resultsPerPage;
        $this->paginationTypes       .= 'i';

        return "LIMIT $offset ?";
    }
}
