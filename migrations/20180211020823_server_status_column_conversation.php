<?php

use Phinx\Migration\AbstractMigration;

class ServerStatusColumnConversation extends AbstractMigration
{
    public function up()
    {
        $serversTable = $this->table('servers');
        $serversTable
            ->addColumn('is_official_server', 'boolean', [
                'after' => 'updated',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this server is capable of hosting official matches',
            ])
            ->addColumn('is_replay_server', 'boolean', [
                'after' => 'is_official_server',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this server is dedicated to serving replays of matches',
            ])
            ->addColumn('is_inactive', 'boolean', [
                'after' => 'is_replay_server',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this server is no longer active but still required for historical purposes',

            ])
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'is_inactive',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this server has been soft deleted',
            ])
            ->update()
        ;

        // BZiON 0.10.x and below required that you soft deleted servers so they'd no longer appear on the server list.
        // For this reason and because Leagues United is the only known installation, we'll be assuming that deleted
        // servers were just meant to be marked as "inactive."
        $this->query("UPDATE servers SET is_inactive = 1 WHERE status = 'deleted';");

        $serversTable
            ->removeColumn('status')
            ->update()
        ;
    }

    public function down()
    {
        $serversTable = $this->table('servers');
        $serversTable
            ->addColumn('status', 'set', [
                'values' => ['active', 'disabled', 'deleted'],
                'null' => false,
                'default' => 'active',
                'comment' => 'The status of the server relative to BZiON',
            ])
            ->update()
        ;

        $this->query("UPDATE servers SET status = 'deleted' WHERE is_inactive = 1;");

        $serversTable
            ->removeColumn('is_official_server')
            ->removeColumn('is_replay_server')
            ->removeColumn('is_inactive')
            ->removeColumn('is_deleted')
            ->update()
        ;
    }
}
