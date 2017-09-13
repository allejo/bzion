<?php


use Phinx\Migration\AbstractMigration;

class DropApiKeysOnPlayerWipe extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function change()
    {
        $apiKeys = $this->table('api_keys');
        $apiKeys
            ->dropForeignKey('owner')
            ->addForeignKey('owner', 'players', 'id', [
                'delete' => 'CASCADE'
            ])
            ->update()
        ;
    }
}
