<?php
/**
 * This file contains an interface for form creators
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

/**
 * An interface for form creator classes
 */
interface FormCreatorInterface
{
    /**
     * Create the form
     * @return Form
     */
    public function create();
}
