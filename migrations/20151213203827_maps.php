<?php

use Phinx\Db\Table\Column;
use Phinx\Migration\AbstractMigration;

class Maps extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function change()
    {
        $maps = $this->table('maps', array('id' => false, 'primary_key' => 'id'));
        $maps->addColumn('id', 'integer', array('limit' => 10, 'signed' => false, 'identity' => true))
            ->addColumn('name', 'string', array('limit' => 42, 'null' => false, 'comment' => 'The name of the map'))
            ->addColumn('alias', 'string', array('limit' => 42, 'null' => true, 'comment' => 'The alias of the map'))
            ->addColumn('avatar', 'string', array('limit' => 200, 'null' => true, 'comment' => 'The path to the map\'s image'))
            ->addColumn('description', 'text', array('null' => false, 'comment' => 'A description of the map'))
            ->addColumn('status', 'set', array('values' => array('active', 'hidden', 'disabled', 'deleted'), 'null' => false,
                'default' => 'active', 'comment' => 'The status of the map'))
            ->addIndex('alias', array('unique' => true))
            ->create();


        $permissions = $this->table('permissions');
        $data = [
            [ 'name' => 'add_map',  'description' => 'The ability to add a map' ],
            [ 'name' => 'edit_map', 'description' => 'The ability to edit a map' ],
            [ 'name' => 'del_map',  'description' => 'The ability to mark a map as deleted' ],
            [ 'name' => 'wipe_map', 'description' => 'The ability to wipe a map from the database' ],
        ];
        $permissions->insert($data)->save();

        $ids = $this->getPermissionIDs();
        $rolePermissions = $this->table('role_permission');
        $data = [ // We use hardcoded role IDs because class constants might change
            [ 'role_id' => 1, 'perm_id' => $ids['add']], // Developers
            [ 'role_id' => 1, 'perm_id' => $ids['edit']],
            [ 'role_id' => 1, 'perm_id' => $ids['del']],
            [ 'role_id' => 1, 'perm_id' => $ids['wipe']],

            [ 'role_id' => 5, 'perm_id' => $ids['add']], // System Admins
            [ 'role_id' => 5, 'perm_id' => $ids['edit']],
            [ 'role_id' => 5, 'perm_id' => $ids['del']],
            [ 'role_id' => 5, 'perm_id' => $ids['wipe']],
        ];
        $rolePermissions->insert($data)->save();

        $matches = $this->table('matches');
        $matches
            ->addColumn('map', 'integer', array('limit' => 10, 'signed' => false, 'null' => true, 'comment' => 'The map that was played'))
            ->addForeignKey('map', 'maps', 'id', array('delete' => 'SET_NULL'))
            ->save();

        $this->updateMatches();

        // TODO: Uncomment this (commented to prevent loss of data in case of error)
        // $matches->removeColumn('map_played')
        //     ->save();
    }

    /**
     * Get the IDs of the newly entered permissions
     *
     * @return array
     */
    private function getPermissionIDs()
    {
        $return = array();

        foreach(array('add', 'edit', 'del', 'wipe') as $permission) {
            $row = $this->fetchRow("SELECT id FROM permissions WHERE name = '{$permission}_map'");
            $return[$permission] = $row['id'];
        }

        return $return;
    }

    /**
     * Convert map strings to IDs in the matches table, inserting new maps in the maps table
     */
    private function updateMatches()
    {
        $maps = $this->table('maps');

        $storedMaps = $this->fetchAll("SELECT DISTINCT(map_played) AS map FROM matches WHERE map_played REGEXP '^[A-Za-z0-9]+$'");

        if (empty($storedMaps)) {
	    // Prevent fatal error with phinx on older PHP versions
	    return;
        }

        $insert = array();

        foreach($storedMaps as $map) {
            $insert[] = [ 'name' => $map[0], 'alias' => null, 'avatar' => null ];
        }

        $maps->insert($insert)->save();

        foreach($storedMaps as $map) {
            $name = $map[0];
            // $name contains only alphanumeric characters, meaning there can't be any MySQL injection
            $row = $this->fetchRow("SELECT id FROM maps WHERE name = '$name'");
            $this->execute("UPDATE matches SET map = {$row['id']} WHERE map_played='$name'");
        }
    }
}

