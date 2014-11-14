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
    protected $queries;

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
        );
    }

    /**
     * Log a database query
     *
     * @param string $query The query
     */
    public function logQuery($query) {
        $this->queries[] = $query;
    }

    /**
     * Get the queries made to the database
     *
     * @return string[]
     */
    public function getQueries()
    {
        return $this->data['queries'];
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
