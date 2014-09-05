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
     * @param  Player  $player The player in question
     * @param  boolean $showDeleted false to hide deleted models even from admins
     * @return boolean
     */
    public function canBeSeenBy($player, $showDeleted=false)
    {
        if ($this->isDeleted()) {
            if (!$showDeleted) {
                return false;
            }

            // Only admins can see deleted models
            return $this->canBeHardDeletedBy($player);
        }

        if (!$this->isActive()) {
            // Only admins can see hidden models
            return $this->canBeEditedBy($player);
        }

        return true;
    }

    /**
     * Find out whether a player can create a model of this type
     *
     * If possible, prefer to override PermissionModel::getCreatePermission()
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
     * If possible, prefer to override PermissionModel::getEditPermission() and/or
     * PermissionModel::isEditor()
     *
     * @return boolean
     */
    public function canBeEditedBy($player)
    {
        return $player->hasPermission(static::getEditPermission()) || $this->isEditor($player);
    }

    /**
     * Find out whether a player can soft delete the model
     *
     * If possible, prefer to override PermissionModel::getSoftDeletePermission()
     * and/or PermissionModel::isEditor()
     *
     * @return boolean
     */
    public function canBeSoftDeletedBy($player)
    {
        return $player->hasPermission(static::getSoftDeletePermission()) || $this->isEditor($player);
    }

    /**
     * Find out whether a player can delete this model
     *
     * If possible, prefer to override PermissionModel::getHardDeletePermission()
     *
     * @return boolean
     */
    public function canBeHardDeletedBy($player)
    {
        return $player->hasPermission(static::getHardDeletePermission());
    }

    /**
     * Find out whether a player can edit or delete the model even without
     * having the appropriate permissions (for example, a team owner should be
     * able to edit their team)
     *
     * @return boolean
     */
    protected function isEditor($player)
    {
        return false;
    }
}
