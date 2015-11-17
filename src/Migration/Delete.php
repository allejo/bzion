<?php
/**
 * This file contains functionality relating to database migrations
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Migration;

/**
 * A request to delete an object
 */
class Delete extends EditAction
{
    /**
     * {@inheritdoc}
     */
    public function perform($pdo, $table, $column, $id)
    {
        $query = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $query->execute(array($id));
    }
}
