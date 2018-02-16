<?php

use BZIon\Debug\DatabaseQuery;
use Pecee\Pixie\Connection;
use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;
use Pecee\Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * The core query builder used across BZiON for creating and modifying queries for all of our entities.
 *
 * @since 0.11.0
 */
class QueryBuilderFlex extends QueryBuilderHandler
{
    /** @var array An array of values that'll be injected into returned database results */
    protected $injectedValues = [];

    /** @var string The column name of the column dedicated to storing the name of the model */
    protected $modelNameColumn;

    /** @var Model|string The FQN of the model object this QueryBuilder instance is for */
    protected $modelType = null;

    /** @var int The amount of results per page with regards to result pagination */
    private $resultsPerPage;

    //
    // Factories
    //

    /**
     * Create a bare QueryBuilder instance.
     *
     * @throws Exception
     *
     * @return static
     */
    final public static function createBuilder()
    {
        Database::getInstance();

        $connect = Service::getQueryBuilderConnection();

        return (new static($connect));
    }

    /**
     * Create a QueryBuilder instance for a specific table.
     *
     * @param  string $tableName
     *
     * @throws Exception If there is no database connection configured.
     *
     * @return static
     */
    final public static function createForTable(string $tableName)
    {
        return self::createBuilder()
            ->table($tableName)
        ;
    }

    /**
     * Creeate a QueryBuilder instance to work with a Model.
     *
     * @param  string $modelType The FQN for the model that
     *
     * @throws Exception If there is no database connection configured.
     *
     * @return static
     */
    final public static function createForModel(string $modelType)
    {
        return self::createBuilder()
            ->table(constant("$modelType::TABLE"))
            ->setModelType($modelType)
        ;
    }

    //
    // Overridden QueryBuilder Functions
    //

    /**
     * {@inheritdoc}
     */
    public function __construct(Connection $connection = null)
    {
        parent::__construct($connection);

        $this->setFetchMode(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     *
     * @internal Use one of the QueryBuilderFlex get*() methods instead.
     *
     * @see self::getArray()
     * @see self::getModels()
     * @see self::getNames()
     */
    public function get(): array
    {
        $queryObject = $this->getQuery();
        $debug = new DatabaseQuery($queryObject->getSql(), $queryObject->getBindings());

        /** @var array $results */
        $results = parent::get();

        $debug->finish($results);

        foreach ($results as &$result) {
            $result = array_merge($this->injectedValues, $result);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function limit($limit): IQueryBuilderHandler
    {
        $this->resultsPerPage = $limit;

        return parent::limit($limit);
    }

    /**
     * {@inheritdoc}
     */
    protected function whereHandler($key, string $operator = null, $value = null, $joiner = 'AND'): IQueryBuilderHandler
    {
        // For certain type of objects, we convert them into something the query builder can handle correctly
        if ($value instanceof BaseModel) {
            $value = $value->getId();
        }
        elseif ($value instanceof DateTime) {
            $value = (string)$value;
        }

        return parent::whereHandler($key, $operator, $value, $joiner);
    }

    //
    // QueryBuilderFlex unique functions
    //

    /**
     * Request that only non-deleted Models should be returned.
     *
     * @return static
     */
    public function active(): QueryBuilderFlex
    {
        $type = $this->modelType;

        // Since it's a system model, values are always handled by BZiON core meaning there will always only be "active"
        // values in the database.
        if ($type::SYSTEM_MODEL) {
            return $this;
        }

        $column = $type::DELETED_COLUMN;

        if ($column === null) {
            @trigger_error(
                sprintf('The use of the status column is deprecated. Update the %s model to use the DELETED_* constants.', get_called_class()),
                E_USER_DEPRECATED
            );

            return $this->whereIn('status', $type::getActiveStatuses());
        }

        $stopPropagation = $type::getActiveModels($this);

        if ($stopPropagation) {
            return $this;
        }

        return $this->whereNot($column, '=', $type::DELETED_VALUE);
    }

    /**
     * An alias for QueryBuilder::getModels(), with fast fetching on by default and no return of results.
     *
     * @param  bool $fastFetch Whether to perform one query to load all the model data instead of fetching them one by
     *              one
     *
     * @throws \Pecee\Pixie\Exception
     *
     * @return void
     */
    public function addToCache(bool $fastFetch = true): void
    {
        $this->getModels($fastFetch);
    }

    /**
     * Get the amount of pages this query would have.
     *
     * @throws \Pecee\Pixie\Exception
     *
     * @return int
     */
    public function countPages(): int
    {
        return (int)ceil($this->count() / $this->resultsPerPage);
    }

    /**
     * Request that a specific model is not returned.
     *
     * @param  Model|int $model The ID or model you don't want to get
     *
     * @return static
     */
    public function except($model): QueryBuilderFlex
    {
        if ($model instanceof Model) {
            $model = $model->getId();
        }

        $this->whereNot('id', '=', $model);

        return $this;
    }

    /**
     * Find the first matching model in the database or return an invalid model.
     *
     * @param mixed  $value      The value to search for
     * @param string $columnName The column name we'll be checking
     *
     * @throws \Pecee\Pixie\Exception
     *
     * @return Model
     */
    public function findModel($value, string $columnName = 'id'): Model
    {
        $type = $this->modelType;

        /** @var array $result */
        $result = parent::find($value, $columnName);

        if ($result === null) {
            return $type::get(0);
        }

        return $type::createFromDatabaseResult($result);
    }

    /**
     * Only show results from a specific page.
     *
     * This method will automatically take care of the calculations for a correct OFFSET.
     *
     * @param  int|null $page The page number (or null to show all pages - counting starts from 0)
     *
     * @throws \Pecee\Pixie\Exception
     *
     * @return static
     */
    public function fromPage(int $page = null): QueryBuilderFlex
    {
        if ($page === null) {
            $this->offset($page);

            return $this;
        }

        $page = intval($page);
        $page = ($page <= 0) ? 1 : $page;

        $this->offset((min($page, $this->countPages()) - 1) * $this->resultsPerPage);

        return $this;
    }

    /**
     * Get the results of query as an array.
     *
     * @param array|string $columns
     *
     * @throws \Pecee\Pixie\Exception
     *
     * @return array
     */
    public function getArray($columns): array
    {
        $this->select($columns);

        return $this->get();
    }

    /**
     * Perform the query and get the results as Models.
     *
     * @param  bool $fastFetch Whether to perform one query to load all the model data instead of fetching them one by
     *                         one (ignores cache)
     *
     * @throws \Pecee\Pixie\Exception
     *
     * @return Model[]
     */
    public function getModels(bool $fastFetch = true): array
    {
        /** @var Model $type */
        $type = $this->modelType;

        $modelColumnsToSelect = $type::getEagerColumnsList();

        if (isset($this->statements['joins'])) {
            $modelColumnsToSelect = __::mapValues($modelColumnsToSelect, function ($value, $key, $array) use ($type) {
                return sprintf('%s.%s', $type::TABLE, $value);
            });
        }

        $this->select($modelColumnsToSelect);

        $results = $this->get();

        if ($fastFetch) {
            return $type::createFromDatabaseResults($results);
        }

        return $type::arrayIdToModel(array_column($results, 'id'));
    }

    /**
     * Perform the query and get back the results in an array of names.
     *
     * @throws \Pecee\Pixie\Exception
     * @throws UnexpectedValueException When no name column has been specified
     *
     * @return string[] An array of the type $id => $name
     */
    public function getNames(): array
    {
        if (!$this->modelNameColumn) {
            throw new UnexpectedValueException(sprintf('The name column has not been specified for this query builder. Use %s::setNameColumn().', get_called_class()));
        }

        $this->select(['id', $this->modelNameColumn]);

        $results = $this->get();

        return array_column($results, $this->modelNameColumn, 'id');
    }

    /**
     * Inject variables into the returned database results.
     *
     * These values will be merged in with values returned from database results. Database results will override any
     * injected values.
     *
     * @param array $injection
     *
     * @return QueryBuilderFlex
     */
    public function injectResultValues(array $injection): QueryBuilderFlex
    {
        $this->injectedValues = $injection;

        return $this;
    }

    /**
     * Set the model this QueryBuilder will be working this.
     *
     * This information is used for automatically retrieving table names, eager columns, and lazy columns for these
     * models.
     *
     * @param  string $modelType The FQN of the model this QueryBuilder will be working with
     *
     * @return $this
     */
    public function setModelType(string $modelType = null): QueryBuilderFlex
    {
        $this->modelType = $modelType;

        return $this;
    }

    /**
     * Set the column that'll be used as the human-friendly name of the model.
     *
     * @param string $columnName
     *
     * @return static
     */
    public function setNameColumn(string $columnName): QueryBuilderFlex
    {
        if (!is_subclass_of($this->modelType, NamedModel::class)) {
            throw new LogicException(sprintf('Setting name columns is only supported in models implementing the "%s" interface.', NamedModel::class));
        }

        $this->modelNameColumn = $columnName;

        return $this;
    }

    /**
     * Make sure that Models invisible to a player are not returned.
     *
     * Note that this method does not take PermissionModel::canBeSeenBy() into
     * consideration for performance purposes, so you will have to override this
     * in your query builder if necessary.
     *
     * @param  Player $player      The player in question
     * @param  bool   $showDeleted Use false to hide deleted models even from admins
     *
     * @return static
     */
    public function visibleTo(Player $player, bool $showDeleted = false): QueryBuilderFlex
    {
        $type = $this->modelType;

        if (is_subclass_of($this->modelType, PermissionModel::class) &&
            $player->hasPermission(constant("$type::EDIT_PERMISSION"))
        ) {
            // The player is an admin who can see the hidden models
            if (!$showDeleted) {
                $col = constant("$type::DELETED_COLUMN");

                if ($col !== null) {
                    $this->whereNot($col, '=', constant("$type::DELETED_VALUE"));
                }
            }
        } else {
            return $this->active();
        }

        return $this;
    }
}
