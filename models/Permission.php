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
    private $name;
    private $description;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "permissions";

    public function __construct($perm_name)
    {
        parent::__construct($perm_name, "name");
        if (!$this->valid) return;

        $permission = $this->result;

        $this->name        = $permission['name'];
        $this->description = $permission['description'];
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getName()
    {
        return $this->name;
    }
}