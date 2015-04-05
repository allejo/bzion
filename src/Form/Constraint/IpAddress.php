<?php
/**
 * This file contains a validator constraint for IP address form types
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Ip Address form constraint
 */
class IpAddress extends Constraint
{
    /**
     * @var string
     */
    public $message = '"%address%" is not a valid IP address or hostname.';

    /**
     * @var string
     */
    public $lengthMessage = 'Hostnames should not exceed 255 characters in length.';
}
