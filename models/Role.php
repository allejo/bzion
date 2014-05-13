<?php
/**
 * This file contains functionality relating to roles a player can have on the website to perform certain tasks
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A role a player is assigned
 */
class Role extends Model
{
    /**
     * The name of the role
     * @var string
     */
    private $name;

    /**
     * Whether or not a role is reusable, when a player has a unique role, they will have their own role that isn't
     * reusable by other players
     * @var bool
     */
    private $reusable;

    /**
     * Whether or not the role is protected from being deleted from the web interface
     * @var bool
     */
    private $protected;

    /**
     * An array of permissions a role has
     * @var bool[]
     */
    private $permissions;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "roles";

    /**
     * Create a new Role object
     *
     * @param int $id The role ID
     */
    public function __construct($id)
    {
        parent::__construct($id);
        if (!$this->valid) return;

        $role = $this->result;

        $this->name        = $role['name'];
        $this->reusable    = $role['reusable'];
        $this->protected   = $role['protected'];
        $this->permissions = array();

        $permissions = parent::fetchIds(
            "JOIN role_permission ON role_permission.perm_id = permissions.id WHERE role_permission.role_id = ?", "i",
            array($id), "permissions", "name");

        foreach ($permissions as $permission)
        {
            $this->permissions[$permission] = true;
        }
    }

    /**
     * Add a permission to a role
     *
     * @param string $perm_name The name of the permission to add
     *
     * @return bool Whether or not the operation was successful
     */
    public function addPerm($perm_name)
    {
        return $this->modifyPerm($perm_name, "add");
    }

    /**
     * Get the permissions a role has
     *
     * @return bool[] An array of permissions
     */
    public function getPerms()
    {
        return $this->permissions;
    }

    /**
     * Check whether a role has a specified permission
     *
     * @param string $permission The permission to check for
     *
     * @return bool Whether or not the role has the permission
     */
    public function hasPerm($permission)
    {
        return isset($this->permissions[$permission]);
    }

    /**
     * Revoke a permission from a role
     *
     * @param string $perm_name The permission to remove
     *
     * @return bool Whether or not the operation was successful
     */
    public function removePerm($perm_name)
    {
        return $this->modifyPerm($perm_name, "remove");
    }

    /**
     * Modify a permission a role has by either adding a new one or removing an old one
     *
     * @param string $perm_name The permission to add or remove
     * @param string $action Whether to "add" or "remove" a permission
     *
     * @return bool
     */
    private function modifyPerm($perm_name, $action)
    {
        if (($action == "remove" && !$this->hasPerm($perm_name)) ||
            ($action == "add" && $this->hasPerm($perm_name)))
        {
            return false;
        }

        $permission = new Permission($perm_name);

        if ($permission->isValid())
        {
            if ($action == "add")
            {
                $this->db->query("INSERT INTO role_permissions (role_id, perm_id) VALUES (?, ?)", "ii",
                    array($this->getId(), $permission->getId()));
            }
            else if ($action == "remove")
            {
                $this->db->query("DELETE FROM role_permission WHERE role_id = ? AND perm_id = ? LIMIT 1", "ii",
                    array($this->getId(), $permission->getId()));
            }

            return true;
        }

        return false;
    }

    /**
     * Create a new role
     *
     * @param string $name The name of new role to be created
     * @param bool $reusable Whether or not to have the role
     *
     * @return \Role
     */
    public static function createNewRole($name, $reusable)
    {
        $db = Database::getInstance();

        $db->query("INSERT INTO roles (name, reusable, protected) VALUES (?, ?, ?)", "sii", array($name, $reusable, 0));

        return new Role($db->getInsertId());
    }

    /**
     * Get the roles a player has
     *
     * @param int $user_id The user ID to get the roles for
     *
     * @return Role[] An array of Roles a player belongs to
     */
    public static function getRoles($user_id)
    {
        return parent::arrayIdToModel(
            parent::fetchIds(
                "JOIN player_roles ON player_roles.role_id = roles.id WHERE player_roles.user_id = ?", "i",
                array($user_id), "roles", "id")
        );
    }
}
