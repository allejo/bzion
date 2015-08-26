<?php

use Phinx\Migration\AbstractMigration;

class ApiKeyStatus extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     */
    public function change()
    {
        $apiKeys = $this->table('api_keys');

        $apiKeys->addColumn('status', 'set', array('values' => array('active', 'disabled', 'deleted'), 'null' => false, 'default' => 'active'));
        $apiKeys->save();
    }
}
