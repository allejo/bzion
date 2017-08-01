<?php
/**
 * This file contains the skeleton for team models
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A base team model interface
 * @package    BZiON\Models
 */
interface TeamInterface extends NamedModel
{
    /**
     * Returns the unique identifier of the team
     *
     * @return string|int
     */
    public function getId();

    /**
     * Returns the human-readable name of the team
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the path to the team's avatar
     *
     * @return string
     */
    public function getAvatar();

    /**
     * Returns whether this is a valid object
     *
     * @return bool
     */
    public function isValid();

    /**
     * Returns whether this object supports keeping count of matches
     *
     * @return bool
     */
    public function supportsMatchCount();
}
