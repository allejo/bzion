<?php
/**
 * This file contains a model interface
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A Model that can be managed by users with specific permissions
 * @package BZiON\Models
 */
interface PermissionModel extends ModelInterface
{
    /**
     * Get the permission required to create such a model
     * @return string
     */
    public static function getCreatePermission();

    /**
     * Get the permission required to edit such a model
     * @return string
     */
    public static function getEditPermission();

    /**
     * Get the permission required to mark this model as deleted
     * @return string
     */
    public static function getSoftDeletePermission();

    /**
     * Get the permission required to delete this model from the database
     * @return string
     */
    public static function getHardDeletePermission();
}
