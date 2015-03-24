<?php
/**
 * This file contains functionality relating to database migrations
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Migration;

/**
 * A request to update an object
 */
class Update extends EditAction
{
    /**
     * @var string
     */
    private $data;

    /**
     * Construct new database update request
     *
     * @param string $data The new data to set
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function perform($pdo, $table, $column, $id)
    {
        $query = $pdo->prepare("UPDATE $table SET $column = ? WHERE id = ?");
        $query->execute(array($this->data, $id));
    }
}
