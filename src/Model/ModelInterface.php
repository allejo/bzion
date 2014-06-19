<?php
/**
 * This file contains the skeleton for all of the database objects
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A base database object interface
 * @package    BZiON\Models
 */
interface ModelInterface
{
    /**
     * Delete the object
     *
     * Please note that this does not delete the object entirely from the database,
     * it only hides it from users. You should overload this function if your object
     * does not have a 'status' column which can be set to 'deleted'.
     */
    public function delete();
    /**
     * Permanently delete the object from the database
     */
    public function wipe();

    /**
     * Get an object's database ID
     * @return int The ID
     */
    public function getId();

    /**
     * See if an object is valid
     * @return bool
     */
    public function isValid();

    /**
     * Check if a status of the object is 'deleted'
     * @return bool
     */
    public function isDeleted();

    /**
     * Gets an entity from the supplied slug, which can either be an alias or an ID
     * @param  string|int $slug The object's slug
     * @return Model
     */
    public static function fetchFromSlug($slug);

    /**
     * Generate an invalid object
     *
     * <code>
     *     <?php
     *     $object = Team::invalid();
     *
     *     get_class($object); // Team
     *     $object->isValid(); // false
     * </code>
     * @return Model
     */
    public static function invalid();
}
