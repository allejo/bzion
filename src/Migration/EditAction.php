<?php
/**
 * This file contains functionality relating to database migrations
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Migration;

/**
 * A request to take action on an object, useful when returned from an edit
 * callback
 */
abstract class EditAction
{
    /**
     * Perform the action on the database
     *
     * @param \PDO   $pdo    The database connection
     * @param string $table  The name of the affected table
     * @param string $column The name of the affected column
     * @param int    $id     The ID of the affected entry
     */
    abstract public function perform($pdo, $table, $column, $id);
}
