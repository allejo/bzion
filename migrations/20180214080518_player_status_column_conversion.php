<?php

use Phinx\Migration\AbstractMigration;

class PlayerStatusColumnConversion extends AbstractMigration
{
    public function up()
    {
        $playersTable = $this->table('players');
        $playersTable
            ->addColumn('is_disabled', 'boolean', [
                'after' => 'last_login',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this account has been disabled by an admin and cannot log in',
            ])
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'is_disabled',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this player has been soft-deleted',
            ])
            ->update()
        ;

        $this->query("UPDATE players SET is_deleted = 1 WHERE status = 'deleted';");
        $this->query("UPDATE players SET is_disabled = 1 WHERE status = 'disabled';");

        $playersTable
            ->removeColumn('status')
            ->update()
        ;
    }

    public function down()
    {
        $playersTable = $this->table('players');
        $playersTable
            ->addColumn('status', 'set', [
                'values' => ['active','disabled','deleted','reported','banned','test'],
                'null' => false,
                'default' => 'active',
                'comment' => 'The status of this player',
            ])
            ->update()
        ;

        $this->query("UPDATE players SET status = 'deleted' WHERE is_deleted = 1;");
        $this->query("UPDATE players SET status = 'disabled' WHERE is_disabled = 1;");

        $playersTable
            ->removeColumn('is_disabled')
            ->removeColumn('is_deleted')
            ->update()
        ;
    }
}
