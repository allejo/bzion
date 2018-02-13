<?php

use Phinx\Migration\AbstractMigration;

class TeamStatusColumnConversion extends AbstractMigration
{
    public function up()
    {
        $teamsTable = $this->table('teams');
        $teamsTable
            ->addColumn('is_closed', 'boolean', [
                'after' => 'members',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this team is closed',
            ])
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'is_closed',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this team has been soft deleted',
            ])
            ->update()
        ;

        $this->query("UPDATE teams SET is_closed = 1 WHERE status = 'closed';");
        $this->query("UPDATE teams SET is_deleted = 1 WHERE status = 'deleted';");

        $teamsTable
            ->removeColumn('status')
            ->update()
        ;
    }

    public function down()
    {
        $teamsTable = $this->table('teams');
        $teamsTable
            ->addColumn('status', 'set', [
                'values' => ['open', 'closed', 'disabled', 'deleted'],
                'null' => false,
                'default' => 'open',
                'comment' => 'The status of the team',
            ])
            ->update()
        ;

        $this->query("UPDATE teams SET status = 'deleted' WHERE is_deleted = 1;");
        $this->query("UPDATE teams SET status = 'closed' WHERE is_closed = 1;");

        $teamsTable
            ->removeColumn('is_closed')
            ->removeColumn('is_deleted')
            ->update()
        ;
    }
}
