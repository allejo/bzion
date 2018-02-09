<?php

use Phinx\Migration\AbstractMigration;

class PageStatusConversion extends AbstractMigration
{
    public function up()
    {
        $pagesTable = $this->table('pages');
        $pagesTable
            ->addColumn('is_unlisted', 'boolean', [
                'after' => 'home',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this page should be listed in the secondary navigation',
            ])
            ->addColumn('is_draft', 'boolean', [
                'after' => 'is_unlisted',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the news article is a draft',
            ])
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'is_draft',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not this entry has been soft-deleted',
            ])
            ->update()
        ;

        $this->query("UPDATE pages SET is_deleted = 1 WHERE status = 'deleted';");
        $this->query("UPDATE pages SET is_unlisted = 1 WHERE status = 'revision';");

        $pagesTable
            ->removeColumn('parent_id')
            ->removeColumn('home')
            ->removeColumn('status')
            ->update()
        ;
    }

    public function down()
    {
        $pagesTable = $this->table('pages');
        $pagesTable
            ->addColumn('parent_id', 'integer', [
                'after' => 'id',
                'null' => true,
                'default' => null,
                'length' => 10,
                'comment' => 'The ID of the original page. If this column is set, then it is a revision',
            ])
            ->addColumn('home', 'integer', [
                'after' => 'author',
                'length' => 4,
                'null' => true,
                'default' => null,
                'comment' => '(Deprecated) Whether or not the page is the home page',
            ])
            ->addColumn('status', 'set', [
                'values' => ['live', 'revision', 'disabled', 'deleted'],
                'after' => 'home',
                'null' => false,
                'default' => 'live',
                'comment' => 'The status of this page',
            ])
            ->update()
        ;

        $this->query("UPDATE pages SET status = 'deleted' WHERE is_deleted = 1");
        $this->query("UPDATE pages SET status = 'revision' WHERE is_unlisted = 1");

        $pagesTable
            ->removeColumn('is_unlisted')
            ->removeColumn('is_draft')
            ->removeColumn('is_deleted')
            ->update()
        ;
    }
}
