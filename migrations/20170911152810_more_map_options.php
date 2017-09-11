<?php


use Phinx\Migration\AbstractMigration;

class MoreMapOptions extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function change()
    {
        $maps = $this->table(Map::TABLE);
        $maps
            ->addColumn('shot_count', 'integer', [
                'after' => 'description',
                'limit' => '2',
                'signed' => false,
                'null' => true,
                'comment' => 'The amount of shots this map has'
            ])
            ->addColumn('ricochet', 'boolean', [
                'after' => 'shot_count',
                'null' => true,
                'comment' => 'Whether or not this map has ricochet',
            ])
            ->addColumn('jumping', 'boolean', [
                'after' => 'ricochet',
                'null' => true,
                'comment' => 'Whether or not this map has jumping',
            ])
            ->update()
        ;
    }
}
