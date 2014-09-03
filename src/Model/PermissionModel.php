<?php
/**
 * This file contains an abstract model class for models that refer to permissions
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A Model that can be managed by users with specific permissions
 * @package BZiON\Models
 */
abstract class PermissionModel extends Model
{
    /**
     * Get the permission required to create such a model
     * @return string|null
     */
    public static function getCreatePermission()
    {
        return null;
    }

    /**
     * Get the permission required to edit such a model
     * @return string|null
     */
    public static function getEditPermission()
    {
        return null;
    }

    /**
     * Get the permission required to mark this model as deleted
     * @return string|null
     */
    public static function getSoftDeletePermission()
    {
        return null;
    }

    /**
     * Get the permission required to delete this model from the database
     * @return string|null
     */
    public static function getHardDeletePermission()
    {
        return null;
    }

    /**
     * Find out whether a player should know that a model exists
     *
     * @todo Add checks for hidden ('inactive'), not just deleted models
     * @return boolean
     */
    public function canBeSeenBy($player)
    {
        if ($this->isDeleted()) {
            // Only admins can see deleted or hidden models
            return $this->canBeHardDeletedBy($player);
        }

        return true;
    }

    /**
     * Find out whether a player can create a model of this type
     *
     * @return boolean
     */
    public static function canBeCreatedBy($player)
    {
        return $player->hasPermission(static::getCreatePermission());
    }

    /**
     * Find out whether a player can edit this model
     *
     * @return boolean
     */
    public function canBeEditedBy($player)
    {
        return $player->hasPermission(static::getEditPermission());
    }

    /**
     * Find out whether a player can soft delete the model
     *
     * @return boolean
     */
    public function canBeSoftDeletedBy($player)
    {
        return $player->hasPermission(static::getSoftDeletePermission());
    }

    /**
     * Find out whether a player can delete this model
     *
     * @return boolean
     */
    public function canBeHardDeletedBy($player)
    {
        return $player->hasPermission(static::getHardDeletePermission());
    }
}
