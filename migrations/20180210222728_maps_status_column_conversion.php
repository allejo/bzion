<?php

use Phinx\Migration\AbstractMigration;

class MapsStatusColumnConversion extends AbstractMigration
{
    public function up()
    {
        $mapsTable = $this->table('maps');
        $mapsTable
            ->addColumn('is_inactive', 'boolean', [
                'after' => 'game_mode',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this map has been marked as inactive, meaning a map is no longer in active rotation on servers',
            ])
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'is_inactive',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this map has been soft deleted'
            ])
            ->update()
        ;

        $this->query("UPDATE maps SET is_inactive = 1 WHERE status = 'hidden' OR status = 'disabled';");
        $this->query("UPDATE maps SET is_deleted = 1 WHERE status = 'deleted';");

        $mapsTable
            ->removeColumn('status')
            ->update()
        ;
    }

    public function down()
    {
        $mapsTable = $this->table('maps');
        $mapsTable
            ->addColumn('status', 'set', [
                'values' => ['active', 'hidden', 'disabled', 'deleted'],
                'null' => false,
                'default' => 'active',
                'comment' => 'The status of the map',
            ])
            ->update()
        ;

        $this->query("UPDATE maps SET status = 'hidden' WHERE is_inactive = 1;");
        $this->query("UPDATE maps SET status = 'deleted' WHERE is_deleted = 1;");

        $mapsTable
            ->removeColumn('is_inactive')
            ->removeColumn('is_deleted')
        ;
    }
}
