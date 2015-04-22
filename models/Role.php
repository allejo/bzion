<?php
/**
 * This file contains functionality relating to roles a player can have on the website to perform certain tasks
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A role a player is assigned
 * @package    BZiON\Models
 */
class Role extends Model
{
    const DEVELOPER     = 1;
    const ADMINISTRATOR = 2;
    const COP           = 3;
    const REFEREE       = 4;
    const SYSADMIN      = 5;
    const PLAYER        = 6;
    const PLAYER_NO_PM  = 7;

    /**
     * The name of the role
     * @var string
     */
    protected $name;

    /**
     * Whether or not a role is reusable, when a player has a unique role, they will have their own role that isn't
     * reusable by other players
     * @var bool
     */
    protected $reusable;

    /**
     * Whether or not the role is protected from being deleted from the web interface
     * @var bool
     */
    protected $protected;

    /**
     * Whether or not to display this role on the 'Admins' page where it lists the league's leaders
     * @var bool
     */
    protected $display;

    /**
     * A Font Awesome CSS class for the icon to be used for the group
     *
     * @var string
     */
    protected $displayIcon;

    /**
     * A CSS class that will represent the color of the group in their tab
     *
     * @var string
     */
    protected $displayColor;

    /**
     * The collective name the group will be called if this role is displayed on the 'Admins' page
     * @var string
     */
    protected $displayName;

    /**
     * The order in which the role will be displayed on the 'Admins' page
     * @var int
     */
    protected $displayOrder;

    /**
     * An array of permissions a role has
     * @var bool[]
     */
    protected $permissions;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "roles";

    /**
     * {@inheritDoc}
     */
    protected function assignResult($role)
    {
        $this->name         = $role['name'];
        $this->reusable     = $role['reusable'];
        $this->protected    = $role['protected'];
        $this->display      = $role['display'];
        $this->displayIcon  = $role['display_icon'];
        $this->displayColor = $role['display_color'];
        $this->displayName  = $role['display_name'];
        $this->displayOrder = $role['display_order'];
        $this->permissions  = array();

        $permissions = parent::fetchIds(
            "JOIN role_permission ON role_permission.perm_id = permissions.id WHERE role_permission.role_id = ?", "i",
            array($this->id), "permissions", "name");

        foreach ($permissions as $permission) {
            $this->permissions[$permission] = true;
        }
    }

    /**
     * Check whether or not this role should appear on the "Admins" page
     *
     * @return bool True if the role should be displayed on the "Admins" page
     */
    public function displayAsLeader()
    {
        return (bool) $this->display;
    }

    /**
     * Get the color this role will have as the background in their badge
     *
     * @return string The color this role will have in their badge. If there is no color set, it will return green which has chosen
     *                randomly by a fair dice roll.
     */
    public function getDisplayColor()
    {
        return (!$this->displayAsLeader()) ? null : $this->displayColor;
    }

    /**
     * Get the "display name" of a role. The display name differs from the name of the role where the "Administrators"
     * role can be displayed as "League Council" when the role is used to displayed players assigned to this role.
     *
     * @return string Returns the display name. If the display name is blank, the role name will be returned.
     */
    public function getDisplayName()
    {
        return (empty($this->displayName)) ? $this->getName() : $this->displayName;
    }

    /**
     * Get the order this role should be displayed on the "Admins" page
     *
     * @return int The order the role should be displayed on the "Admins" page
     */
    public function getDisplayOrder()
    {
        return (!$this->displayAsLeader()) ? -1 : $this->displayOrder;
    }

    /**
     * Get the Font Awesome class that will be used as the symbol for the role on the "Admins" page
     *
     * @return string The Font Awesome class for the symbol
     */
    public function getDisplayIcon()
    {
        return (!$this->displayAsLeader()) ? null : $this->displayIcon;
    }

    /**
     * Get an array of players who have this role assigned to them
     *
     * @return Player[] An array of players with this role assigned to them
     */
    public function getUsers()
    {
        return Player::arrayIdToModel(
            parent::fetchIds(
                "JOIN player_roles ON player_roles.role_id = roles.id WHERE player_roles.role_id = ?", "i",
                array($this->getId()), "roles", "player_roles.user_id"
            )
        );
    }

    /**
     * Get the name of the role as displayed in the admin interface
     *
     * @return string The name of the group
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Check if this role is for a group of users or if it's a role for a single user
     *
     * @return bool True if multiple users can be assigned this role
     */
    public function isReusable()
    {
        return (bool) $this->reusable;
    }

    /**
     * Check if this role is protected from being deleted
     *
     * @return bool True if this role is protected from being deleted
     */
    public function isProtected()
    {
        return (bool) $this->protected;
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
     * @param string $action    Whether to "add" or "remove" a permission
     *
     * @return bool
     */
    private function modifyPerm($perm_name, $action)
    {
        if (($action == "remove" && !$this->hasPerm($perm_name)) ||
            ($action == "add" && $this->hasPerm($perm_name))) {
            return false;
        }

        $permission = Permission::getPermissionFromName($perm_name);

        if ($permission->isValid()) {
            if ($action == "add") {
                $this->db->query("INSERT INTO role_permission (role_id, perm_id) VALUES (?, ?)", "ii",
                    array($this->getId(), $permission->getId()));

                $this->permissions[$perm_name] = true;
            } elseif ($action == "remove") {
                $this->db->query("DELETE FROM role_permission WHERE role_id = ? AND perm_id = ? LIMIT 1", "ii",
                    array($this->getId(), $permission->getId()));

                unset($this->permissions[$perm_name]);
            }

            return true;
        }

        return false;
    }

    /**
     * Set the content of the page
     *
     * @param  boolean $display
     * @return self
     */
    public function setDisplayAsLeader($display)
    {
        return $this->updateProperty($this->display, "display", $display, 'i');
    }

    /**
     * Create a new role
     *
     * @param string $name         The name of new role to be created
     * @param bool   $reusable     Whether or not to have the role
     * @param bool   $display      Whether or not to display the role on the 'Admins' page
     * @param string $displayIcon
     * @param string $displayColor
     * @param null   $displayName  The name that will be used on the 'Admins' page, if $display is set to true
     * @param int    $displayOrder The order the role will be displayed on, if $display is set to true
     *
     * @return \Role
     */
    public static function createNewRole($name, $reusable, $display = false, $displayIcon = "", $displayColor = "", $displayName = null, $displayOrder = 0)
    {
        return self::create(array(
            'name'          => $name,
            'reusable'      => $reusable,
            'protected'     => 0,
            'display'       => $display,
            'display_icon'  => $displayIcon,
            'display_color' => $displayColor,
            'display_name'  => $displayName,
            'display_order' => $displayOrder
        ), 'siiisssi');
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
                array($user_id), "roles", "roles.id"
            )
        );
    }

    /**
     * Get the roles that should be displayed on the "Admins" page
     *
     * @return Role[] An array of Roles that should be displayed on the "Admins" page
     */
    public static function getLeaderRoles()
    {
        return parent::arrayIdToModel(
            parent::fetchIds(
                "WHERE display = 1 ORDER BY display_order ASC"
            )
        );
    }
}
