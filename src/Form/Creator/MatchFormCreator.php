<?php
/**
 * This file contains a form creator for Matches
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\MatchTeamType;
use BZIon\Form\Type\DatetimeWithTimezoneType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\LessThan;

/**
 * Form creator for matches
 */
class MatchFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('first_team', new MatchTeamType())
            ->add('second_team', new MatchTeamType())
            ->add('duration', 'choice', array(
                'choices' => array_keys(unserialize(DURATION)),
                'constraints' => new NotBlank(),
                'expanded' => true
            ))
            ->add('server_address', 'text', array(
                'required' => false,
                'attr' => array('placeholder' => 'brad.guleague.org:5100'),
            ))
            ->add('time', new DatetimeWithTimezoneType(), array(
                'constraints' => array(
                    new NotBlank(),
                    new LessThan(array(
                        'value'   => \TimeDate::now()->addMinutes(10),
                        'message' => 'The timestamp of the match must not be in the future'
                    ))
                ),
                'data' => \TimeDate::now(\Controller::getMe()->getTimezone())
            ))
            ->add('enter', 'submit');
    }
}
