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
     * The permission required to create such a model
     * @var string|null
     */
    const CREATE_PERMISSION = null;

    /**
     * The permission required to edit such a model
     * @var string|null
     */
    const EDIT_PERMISSION = null;

    /**
     * The permission required to mark this model as deleted
     * @var string|null
     */
    const SOFT_DELETE_PERMISSION = null;

    /**
     * The permission required to delete this model from the database
     * @var string|null
     */
    const HARD_DELETE_PERMISSION = null;

    /**
     * Find out whether a player should know that a model exists
     *
     * @param  Player  $player      The player in question
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
     * If possible, prefer to override PermissionModel::CREATE_PERMISSION
     *
     * @return boolean
     */
    public static function canBeCreatedBy($player)
    {
        return $player->hasPermission(static::CREATE_PERMISSION);
    }

    /**
     * Find out whether a player can edit this model
     *
     * If possible, prefer to override PermissionModel::EDIT_PERMISSION and/or
     * PermissionModel::isEditor()
     *
     * @return boolean
     */
    public function canBeEditedBy($player)
    {
        return $player->hasPermission(static::EDIT_PERMISSION) || $this->isEditor($player);
    }

    /**
     * Find out whether a player can soft delete the model
     *
     * If possible, prefer to override PermissionModel::SOFT_DELETE_PERMISSION
     * and/or PermissionModel::isEditor()
     *
     * @return boolean
     */
    public function canBeSoftDeletedBy($player)
    {
        return $player->hasPermission(static::SOFT_DELETE_PERMISSION) || $this->isEditor($player);
    }

    /**
     * Find out whether a player can delete this model
     *
     * If possible, prefer to override PermissionModel::HARD_DELETE_PERMISSION
     *
     * @return boolean
     */
    public function canBeHardDeletedBy($player)
    {
        return $player->hasPermission(static::HARD_DELETE_PERMISSION);
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
