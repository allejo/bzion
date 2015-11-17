<?php
/**
 * This file contains functionality relating to database migrations
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Migration;

use Phinx\Db\Table;
use Phinx\Migration\AbstractMigration as BaseMigration;

/**
 * An abstract database migration
 */
class AbstractMigration extends BaseMigration
{
    /**
     * Edit the serialized data of a conversation event
     *
     * @param string   $typeQuery The MySQL string for the event type
     * @param \Closure $callback  The callback function to edit the event. The
     *                            1st parameter should be the data array, while
     *                            the 2nd can be the name of the class. Both
     *                            parameters can be passed by reference and
     *                            altered. The return value can be the desired
     *                            BZIon\Migration\EditAction or null.
     */
    protected function editConversationEvent($typeQuery, $callback)
    {
        return $this->editSerializedData(
            "conversation_events",
            "event",
            "WHERE `type` = '$typeQuery'",
            $callback
        );
    }

    /**
     * Edit the serialized data of a conversation event
     *
     * @param string   $table      The name of the table to edit
     * @param string   $column     The name of the column to edit
     * @param string   $extraQuery An extra MySQL string for the query
     * @param \Closure $callback   The callback function to edit the event. The
     *                             1st parameter should be the data array, while
     *                             the 2nd can be the name of the class. Both
     *                             parameters can be passed by reference and
     *                             altered. The return value can be the desired
     *                             BZIon\Migration\EditAction or null.
     */
    protected function editSerializedData($table, $column, $extraQuery, $callback)
    {
        if ($table instanceof Table) {
            $table = $table->getName();
        }

        $query = "SELECT id, `$column` FROM $table $extraQuery";
        $rows = $this->fetchAll($query);

        foreach ($rows as $row) {
            list($class, $array) = $this->unserializeClass($row[$column]);

            $action = $callback($array, $class);

            if (!$action instanceof EditAction) {
                $data = $this->serializeClass($class, $array);
                $action = new Update($data);
            }

            $pdo = $this->getAdapter()->getConnection();
            $action->perform($pdo, $table, $column, $row['id']);
        }
    }

    /**
     * Given a serialized PHP class, unserialize it to its components
     *
     * @param  string $serialized The serialized class
     * @return array  An array containing the full name of the class and an
     *                array of its data
     */
    private function unserializeClass($serialized)
    {
        $matches = array();
        preg_match('/^C:\d+:"(.+)":\d+:{(.*)}$/', $serialized, $matches);

        $class = $matches[1];
        $array = unserialize($matches[2]);

        return array($class, $array);
    }

    /**
     * Serialize a PHP class given its name and data
     *
     * @param  string $class The full name of the class
     * @param  array  $array The data of the class
     * @return string The serialized class
     */
    private function serializeClass($class, $array)
    {
        $serialized = serialize($array);

        return sprintf('C:%d:"%s":%d:{%s}',
            strlen($class),
            $class,
            strlen($serialized),
            $serialized
        );
    }
}
