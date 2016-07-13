<?php
/**
 * This file contains functionality related to MySQL query debugging, logging and timing actions
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Debug;

/**
 * A MySQL query that will be profiled on a development environment
 */
class DatabaseQuery
{
    /**
     * The MySQL query
     * @var string
     */
    private $query;

    /**
     * The first keyword of the query (e.g. SELECT)
     * @var string
     */
    private $queryType;

    /**
     * The parameters of the query
     * @var array|null
     */
    private $params;

    /**
     * The name of the profiler event
     * @var string
     */
    private $eventName;

    /**
     * The starting UNIX timestamp of the query
     * @var float
     */
    private $startTime;

    /**
     * The UNIX timestamp of the time when the query was completed
     * @var float
     */
    private $finishTime;

    /**
     * The duration of the query in microseconds
     * @var float
     */
    private $duration;

    /**
     * The results of the query
     * @var mixed
     */
    private $results;

    /**
     * @var int
     */
    const MICROSECONDS_IN_SECOND = 1000000;

    /**
     * Debug a database query
     *
     * @param string     $query  The MySQL query
     * @param array|null $params The query parameters
     */
    public function __construct(&$query, &$params)
    {
        $this->query  = $query;
        $this->params = ($params !== false) ? array_values($params) : null;

        $this->queryType = strtok($query, ' ');
        $this->eventName = 'database.query.' . $this->queryType;

        Debug::startStopwatch($this->eventName);

        if (\Service::isDebug()) {
            $this->startTime = microtime(true);
        }
    }

    /**
     * Mark a query as finished
     *
     * @param  mixed $return The returned values of the query
     * @return void
     */
    public function finish(&$return)
    {
        $duration = Debug::finishStopwatch($this->eventName);
        Debug::log("Database {$this->queryType} query", array(
            "query"    => $this->query,
            "params"   => $this->params,
            "duration" => "$duration ms"
        ), 'mysql');

        if (\Service::isDebug()) {
            $this->finishTime = microtime(true);
            $this->duration = ($this->finishTime - $this->startTime) * self::MICROSECONDS_IN_SECOND;

            $this->results = $return;

            $collector = \Service::getContainer()->get('data_collector.bzion_database_collector', null);
            if ($collector) {
                $collector->logQuery($this);
            }
        }
    }

    /**
     * Get the value the MySQL query
     *
     * @param  bool $highlight Whether HTML should be used to highlight to the query
     * @return string
     */
    public function getQuery($highlight = false)
    {
        if ($highlight) {
            return \SqlFormatter::highlight($this->query);
        } else {
            return $this->query;
        }
    }

    /**
     * Get the first keyword of the query (e.g. SELECT)
     *
     * @return string
     */
    public function getQueryType()
    {
        return $this->queryType;
    }

    /**
     * Get the parameters of the query
     *
     * Alias for DatabaseQuery::getParams()
     *
     * @return array|null
     */
    public function getParams()
    {
        return $this->getParameters();
    }

    /**
     * Get the parameters of the query
     *
     * @return array|null
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Get the duration of the query in microseconds
     *
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Get the query results
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Get the query string parts
     *
     * @param  bool $highlight Whether HTML should be used to highlight to the query
     * @return array
     */
    public function getQueryParts($highlight = false)
    {
        return explode("?", $this->getQuery($highlight));
    }

    /**
     * Get the resolved query strings (where all `?`s are replaced with the
     * actual parameter values)
     *
     * @param  bool $highlight Whether HTML should be used to highlight to the query
     * @return string
     */
    public function getResolvedQuery($highlight = false)
    {
        $query = '';

        foreach ($this->getQueryParts($highlight) as $i => $part) {
            $query .= $part;

            if (array_key_exists($i, $this->params)) {
                // We are not going to execute the query, so there is no need
                // to use anything safer than mysql_escape_string()
                $param = mysql_escape_string($this->params[$i]);

                if ($param === null) {
                    $query .= 'null';
                } elseif (is_string($param)) {
                    // Strings will be quoted
                    $query .= '"' . $param . '"';
                } else {
                    $query .= $param;
                }
            }
        }

        return $query;
    }
}
