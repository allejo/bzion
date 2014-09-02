<?php
/**
 * This file contains a form creator for player profiles
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\TimezoneType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Form creator to allow users to edit their profiles
 */
class ProfileFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('description', 'textarea', array(
                'constraints' => new Length(array('max' => 8000)),
                'data' => $this->editing->getDescription(),
                'required' => false
            ))
            ->add('country', 'choice', array(
                'choices' => \Country::getCountriesWithISO(),
                'data' => $this->editing->getCountry()->getISO()
            ))
            ->add('timezone', new TimezoneType(), array(
                'constraints' => new NotBlank(),
                'data' => $this->editing->getTimezone()
            ))
            ->add('enter', 'submit');
    }
}
