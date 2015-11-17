<?php
/**
 * This file contains a database migration
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Migration\AbstractMigration;

class HostnameBans extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('banned_ips');

        foreach ($table->getColumns() as $column) {
            if ($column->getName() === 'ip_address') {
                $column->setLimit(255);
                $column->setComment('The IP address or hostname wildcard that was banned. IP addresses are should be in the IPv4 format due to BZFlag only supporting IPv4');

                $table->changeColumn($column->getName(), $column);
                break;
            }
        }

        $table->addIndex(array('ban_id', 'ip_address'), array('unique' => true))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}
