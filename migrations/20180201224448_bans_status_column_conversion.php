<?php


use Phinx\Migration\AbstractMigration;

class BansStatusColumnConversion extends AbstractMigration
{
    public function up()
    {
        $bansTable = $this->table('bans');
        $bansTable
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'is_soft_ban',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the ban has been deleted',
            ])
            ->update()
        ;

        $this->query("UPDATE bans SET is_deleted = 1 WHERE status = 'deleted';");

        $bansTable
            ->removeColumn('status')
            ->update()
        ;
    }

    public function down()
    {
        $bansTable = $this->table('bans');
        $bansTable
            ->addColumn('status', 'set', [
                'values' => ['public', 'hidden', 'deleted'],
                'after' => 'is_soft_ban',
                'null' => false,
                'default' => 'public',
                'comment' => 'The status of the ban element',
            ])
            ->update()
        ;

        $this->query("UPDATE bans SET status = 'deleted' WHERE is_deleted = true;");

        $bansTable
            ->removeColumn('is_deleted')
            ->update()
        ;
    }
}
