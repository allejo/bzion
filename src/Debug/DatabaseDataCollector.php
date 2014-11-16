<?php
/**
 * This file contains a data collector for the profiler
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Debug;

use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A data collector that collects database data that will be shown on the
 * profiler
 */
class DatabaseDataCollector implements DataCollectorInterface
{
    public $data;
    protected $queries = array();
    protected $cacheFetches = array();

    /**
     * Collects data for the given Request and Response, so that it can be
     * serialized and stored for later retrieval
     *
     * @param Request    $request   A Request instance
     * @param Response   $response  A Response instance
     * @param \Exception $exception An Exception instance
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'queries' => $this->queries,
            'cachedModels' => \Service::getModelCache()->all(),
            'cacheFetches' => $this->cacheFetches
        );
    }

    /**
     * Log a database query
     *
     * @param DatabaseQuery $query The query
     */
    public function logQuery($query)
    {
        $this->queries[] = $query;
    }

    /**
     * Log a fetch from the Model Cache
     *
     * @param string $type The type of the model
     * @param int    $id   The ID of the model
     */
    public function logCacheFetch($type, $id)
    {
        if (isset($this->cacheFetches[$type])) {
            $this->cacheFetches[$type]++;
        } else {
            $this->cacheFetches[$type] = 1;
        }
    }

    /**
     * Get the sorted catche fetches by Model type
     *
     * @return array
     */
    public function getCacheFetches()
    {
        arsort($this->data['cacheFetches']);
        return $this->data['cacheFetches'];
    }

    /**
     * Get the total number of catche fetches
     *
     * @return int
     */
    public function getTotalCacheFetches()
    {
        return array_sum($this->data['cacheFetches']);
    }

    /**
     * Get an estimate of the time that the model cache saved in milliseconds
     *
     * @return number
     */
    public function estimateTimeSaved()
    {
        $sum = 0;
        $count = 0;

        foreach($this->data['queries'] as $query) {
            if (0 === strpos(trim($query->getQuery()), 'SELECT *')) {
                $sum += $query->getDuration();
                $count++;
            }
        }

        if ($count == 0) {
            return 0;
        } else {
            return array_sum($this->data['cacheFetches']) * $sum/$count/1000;
        }
    }

    /**
     * Get the number of times each query was sent to the server
     *
     * @return array An array with resolved queries as keys and frequencies as values
     */
    public function getQueryFrequencies()
    {
        $return = array();

        foreach($this->data['queries'] as $query) {
            $resolved = $query->getResolvedQuery();

            if (!isset($return[$resolved])) {
                $return[$resolved] = 1;
            } else {
                $return[$resolved]++;
            }
        }

        return $return;
    }

    /**
     * Get the number of duplicated queries
     *
     * @return int
     */
    public function getDuplicatedQueryCount()
    {
        $frequencies = $this->getQueryFrequencies();

        return array_sum($frequencies) - count($frequencies);
    }

    /**
     * Get the queries made to the database
     *
     * @return DatabaseQuery[]
     */
    public function getQueries()
    {
        return $this->data['queries'];
    }

    /**
     * Get the total duration of the database queries in milliseconds
     *
     * @return float
     */
    public function getDuration()
    {
        $sum = 0;

        foreach ($this->data['queries'] as $query) {
            $sum += $query->getDuration();
        }

        return $sum / 1000;
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName()
    {
        return 'database';
    }
}
