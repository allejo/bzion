<?php
/**
 * This file contains a class to quickly generate database queries for notifications
 *
 * @package    BZiON\Models\QueryBuilder
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * This class can be used to search for notifications with specific characteristics in
 * the database and perform a limited set of actions on them.
 *
 * @package    BZiON\Models\QueryBuilder
 */
class NotificationQueryBuilder extends QueryBuilder
{
    /**
     * Mark all the selected notifications as read
     * @return self
     */
    public function markRead()
    {
        $cloned = clone $this;

        $cloned->where('status')->equals('unread');

        $type   = $cloned->type;
        $table  = $type::TABLE;
        $params = $cloned->createQueryParams();

        ld($cloned->parameters);

        Database::getInstance()->query(
            "UPDATE `$table` SET `status`='read' WHERE id IN ( SELECT id FROM ( SELECT id FROM `$table` $params) tmp)",
            $cloned->types,
            $cloned->parameters
        );

        return $this;
    }
}
