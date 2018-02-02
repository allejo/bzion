<?php


use Phinx\Migration\AbstractMigration;

class AllowCountryDeletion extends AbstractMigration
{
    public function change()
    {
        $countriesTable = $this->table('countries');
        $countriesTable
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'name',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the country has been deleted',
            ])
            ->update()
        ;
    }
}
