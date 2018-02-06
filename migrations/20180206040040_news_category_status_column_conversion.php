<?php

use Phinx\Migration\AbstractMigration;

class NewsCategoryStatusColumnConversion extends AbstractMigration
{
    public function up()
    {
        $newsCategoryTable = $this->table('news_categories');
        $newsCategoryTable
            ->addColumn('is_read_only', 'boolean', [
                'after' => 'protected',
                'null' => false,
                'default' => false,
                'comment' => 'When set to true, no new articles should be able to use this category'
            ])
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'is_read_only',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the news category has been soft deleted',
            ])
            ->changeColumn('protected', 'boolean', [
                'default' => false,
                'null' => false,
                'comment' => 'When set to true, prevents the category from being deleted from the UI',
            ])
            ->update()
        ;

        $this->query("UPDATE news_categories SET is_deleted = 1 WHERE status = 'deleted';");

        $newsCategoryTable
            ->removeColumn('status')
            ->renameColumn('protected', 'is_protected')
            ->update()
        ;
    }

    public function down()
    {
        $newsCategoryTable = $this->table('news_categories');
        $newsCategoryTable
            ->addColumn('status', 'set', [
                'values' => ['enabled', 'disabled', 'deleted'],
                'after' => 'is_deleted',
                'null' => false,
                'default' => 'enabled',
                'comment' => 'The status of the news element',
            ])
            ->renameColumn('is_protected', 'protected')
            ->update()
        ;

        $this->query("UPDATE news_categories SET status = 'deleted' WHERE is_deleted = 1;");

        $newsCategoryTable
            ->removeColumn('is_deleted')
            ->removeColumn('is_read_only')
            ->update()
        ;
    }
}
