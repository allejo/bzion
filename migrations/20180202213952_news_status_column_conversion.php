<?php


use Phinx\Migration\AbstractMigration;

class NewsStatusColumnConversion extends AbstractMigration
{
    public function up()
    {
        $newsTable = $this->table('news');
        $newsTable
            ->addColumn('is_draft', 'boolean', [
                'after' => 'editor',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the news article is a draft',
            ])
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'is_draft',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the news article has been soft deleted',
            ])
            ->update()
        ;

        $this->query("UPDATE news SET is_draft = 1 WHERE status = 'revision' OR status ='draft';");
        $this->query("UPDATE news SET is_deleted = 1 WHERE status = 'deleted' OR status = 'disabled';");

        $newsTable
            ->removeColumn('parent_id')
            ->removeColumn('status')
            ->update()
        ;
    }

    public function down()
    {
        $newsTable = $this->table('news');
        $newsTable
            ->addColumn('parent_id', 'integer', [
                'after' => 'id',
                'null' => true,
                'default' => null,
                'length' => 11,
                'comment' => 'The ID of the original news post. If this column is set, then it is a revision',
            ])
            ->addColumn('status', 'set', [
                'values' => ['published', 'revision', 'draft', 'disabled', 'deleted'],
                'after' => 'editor',
                'null' => false,
                'default' => 'published',
                'comment' => 'The status of the news element',
            ])
            ->update()
        ;

        $this->query("UPDATE news SET status = 'draft' WHERE is_draft = 1;");
        $this->query("UPDATE news SET status = 'deleted' WHERE is_deleted = 1;");

        $newsTable
            ->removeColumn('is_draft')
            ->removeColumn('is_deleted')
            ->update()
        ;
    }
}
