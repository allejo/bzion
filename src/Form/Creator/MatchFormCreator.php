<?php
/**
 * This file contains a form creator for Matches
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\DatetimeWithTimezoneType;
use BZIon\Form\Type\MatchTeamType;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for matches
 */
class MatchFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        $durations = \Service::getParameter('bzion.league.duration');
        foreach ($durations as $duration => &$value) {
            $durations[$duration] = $duration;
        }

        return $builder
            ->add('first_team', new MatchTeamType())
            ->add('second_team', new MatchTeamType())
            ->add('duration', 'choice', array(
                'choices'     => $durations,
                'constraints' => new NotBlank(),
                'expanded'    => true
            ))
            ->add('server_address', 'text', array(
                'required' => false,
                'attr'     => array('placeholder' => 'brad.guleague.org:5100'),
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

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        $firstTeam  = $form->get('first_team');
        $secondTeam = $form->get('second_team');

        $firstPlayers  = array_map($this->getModelToID(),  $firstTeam->get('participants')->getData());
        $secondPlayers = array_map($this->getModelToID(), $secondTeam->get('participants')->getData());

        $serverInfo = explode(':', $form->get('server_address')->getData());
        if (!isset($serverInfo[1])) {
            $serverInfo[1] = 5154;
        }

        $match = \Match::enterMatch(
            $firstTeam->get('team')->getData()->getId(),
            $secondTeam->get('team')->getData()->getId(),
            $firstTeam->get('score')->getData(),
            $secondTeam->get('score')->getData(),
            $form->get('duration')->getData(),
            $this->me->getId(),
            $form->get('time')->getData(),
            $firstPlayers,
            $secondPlayers,
            $serverInfo[0],
            $serverInfo[1]
        );

        return $match;
    }

    /**
     * Get a function which converts models to their IDs
     *
     * Useful to store the match players into the database
     */
    private static function getModelToID()
    {
        return function ($model) {
            return $model->getId();
        };
    }
}
