<?php
/**
 * This file contains a form creator for player profiles
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\TimezoneType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
        $emailConstraints = array(new Length(array('max' => 255)));
        if (!\Service::isDebug()) {
            // Don't validate e-mails when developing, for example to allow
            // messaging anyone@localhost
            $emailConstraints[] = new Email();
        }

        return $builder
            ->add('description', 'textarea', array(
                'constraints' => new Length(array('max' => 8000)),
                'data'        => $this->editing->getDescription(),
                'required'    => false
            ))
            ->add('avatar', 'file', array(
                'constraints' => new Image(array(
                    'minWidth'  => 50,
                    'minHeight' => 50,
                    'maxSize'   => '4M'
                )),
                'required' => false
            ))
            ->add('delete_avatar', 'submit')
            ->add('country', 'choice', array(
                'choices'  => \Country::getCountriesWithISO(),
                'data'     => $this->editing->getCountry()->getISO(),
                'required' => false
            ))
            ->add('email', 'email', array(
                'constraints' => $emailConstraints,
                'data'        => $this->editing->getEmailAddress(),
                'label'       => 'E-Mail Address',
                'required'    => false
            ))
            // TODO: Disable this when no e-mail has been specified with JS
            ->add('receive', 'choice', array(
                'choices' => array(
                    'nothing'    => 'Nothing',
                    'messages'   => 'Messages only',
                    'everything' => 'Everything'
                ),
                'data'        => $this->editing->getReceives(),
                'label'       => 'Receive notifications about',
                'expanded'    => true,
                'placeholder' => false,
                'required'    => false
            ))
            ->add('timezone', new TimezoneType($this->editing->getTimezone()), array(
                'constraints' => new NotBlank(),
                'data'        => $this->editing->getTimezone()
            ))
            ->add('enter', 'submit');
    }
}
