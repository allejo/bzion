<?php
/**
 * This file contains functionality relating to permissions that roles have
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A permission that is assigned to a role
 */
class Permission extends Model
{
    /**
     * The name of the permission
     * @var string
     */
    private $name;

    /**
     * The description of the permission
     * @var string
     */
    private $description;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "permissions";

    /**
     * Construct a new Permission
     *
     * @param int $perm_name The name of the permission
     */
    public function __construct($perm_name)
    {
        parent::__construct($perm_name, "name");
        if (!$this->valid) return;

        $permission = $this->result;

        $this->name        = $permission['name'];
        $this->description = $permission['description'];
    }

    /**
     * Get the description of the permission
     * @return string The description of the permission
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the name of the permission
     * @return string The name of the permission
     */
    public function getName()
    {
        return $this->name;
    }
}