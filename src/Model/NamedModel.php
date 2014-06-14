<?php
/**
 * This file contains a model interface
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A Model that has a name
 * @package    BZiON\Models
 */
interface NamedModel extends ModelInterface
{
    /**
     * Get the name of the entity
     * @return string
     */
    public function getName();
}
