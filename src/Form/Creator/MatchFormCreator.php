<?php
/**
 * This file contains a form creator for Matches
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\DatetimeWithTimezoneType;
use BZIon\Form\Type\MatchTeamType;
use BZIon\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
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
            ->add('first_team', new MatchTeamType(), array(
                'disableTeam' => $this->isEdit() && $this->editing->isOfficial()
            ))
            ->add('second_team', new MatchTeamType(), array(
                'disableTeam' => $this->isEdit() && $this->editing->isOfficial()
            ))
            ->add('duration', ChoiceType::class, array(
                'choices'     => $durations,
                'constraints' => new NotBlank(),
                'expanded'    => true
            ))
            ->add('server', new ModelType('Server'), [
                'constraints' => new NotBlank(),
                'choice_label' => function ($value) {
                    $server = \Server::get($value);
                    return $server->isValid() ? $server->getAddress() : '';
                }
            ])
            ->add('time', new DatetimeWithTimezoneType(), array(
                'constraints' => array(
                    new NotBlank(),
                    new LessThan(array(
                        'value'   => \TimeDate::now()->addMinutes(10),
                        'message' => 'The timestamp of the match must not be in the future'
                    ))
                ),
                'data' => ($this->isEdit())
                    ? $this->editing->getTimestamp()->setTimezone(\Controller::getMe()->getTimezone())
                    : \TimeDate::now(\Controller::getMe()->getTimezone()),
                'with_seconds' => $this->isEdit()
            ))
            ->add('map', new ModelType('Map'), array(
                'required' => false
            ))
            ->add('type', ChoiceType::class, array(
                'choices'  => array(
                    \Match::OFFICIAL => 'Official',
                    \Match::FUN => 'Fun match',
                    \Match::SPECIAL => 'Special event match',
                ),
                'disabled' => $this->editing && $this->editing->isOfficial(),
                'label' => 'Match Type'
            ))
            ->add('enter', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Match $match
     */
    public function fill($form, $match)
    {
        $form->get('first_team')->setData(array(
            'team'         => $match->getTeamA(),
            'participants' => $match->getTeamAPlayers(),
            'score'        => $match->getTeamAPoints()
        ));
        $form->get('second_team')->setData(array(
            'team'         => $match->getTeamB(),
            'participants' => $match->getTeamBPlayers(),
            'score'        => $match->getTeamBPoints()
        ));

        $form->get('duration')->setData($match->getDuration());
        $form->get('server')->setData($match->getServer());
        $form->get('time')->setData($match->getTimestamp());
        $form->get('map')->setData($match->getMap());
        $form->get('type')->setData($match->getMatchType());
    }

    /**
     * {@inheritdoc}
     *
     * @param \Match $match
     */
    public function update($form, $match)
    {
        if (($match->getDuration() != $form->get('duration')->getData())
            || $match->getTimestamp()->ne($form->get('time')->getData())) {
            // The timestamp of the match was changed, we might need to
            // recalculate its ELO
            $this->controller->recalculateNeeded = true;
        }

        $firstTeam  = $form->get('first_team');
        $secondTeam = $form->get('second_team');

        if (!$match->isOfficial()) {
            $match->setTeamColors(
                $firstTeam->get('team')->getData(),
                $secondTeam->get('team')->getData()
            );
        }

        $match->setTeamPlayers(
            $this->getPlayerList($firstTeam),
            $this->getPlayerList($secondTeam)
        );

        $match->setTeamPoints(
            $firstTeam->get('score')->getData(),
            $secondTeam->get('score')->getData()
        );

        $match
            ->setDuration($form->get('duration')->getData())
            ->setServer($form->get('server')->getData()->getId())
            ->setTimestamp($form->get('time')->getData())
            ->setMap($form->get('map')->getData()->getId());

        if (!$match->isEloCorrect()) {
            $this->controller->recalculateNeeded = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        $firstTeam  = $form->get('first_team');
        $secondTeam = $form->get('second_team');

        $firstId = $firstTeam->get('team')->getData()->getId();
        $secondId = $secondTeam->get('team')->getData()->getId();

        $a_color = \ColorTeam::isValidTeamColor($firstId) ? $firstId : null;
        $b_color = \ColorTeam::isValidTeamColor($secondId) ? $secondId : null;

        $official = ($form->get('type')->getData() === \Match::OFFICIAL);

        $match = \Match::enterMatch(
            $official ? $firstId : null,
            $official ? $secondId : null,
            $firstTeam->get('score')->getData(),
            $secondTeam->get('score')->getData(),
            $form->get('duration')->getData(),
            $this->me->getId(),
            $form->get('time')->getData(),
            $this->getPlayerList($firstTeam),
            $this->getPlayerList($secondTeam),
            $form->get('server_address')->getData(),
            null,
            $form->get('map')->getData()->getId(),
            $form->get('type')->getData(),
            $a_color,
            $b_color
        );

        $match
            ->setServer($form->get('server')->getData()->getId());

        return $match;
    }

    /**
     * Get the player list of a team
     *
     * @param FormInterface $team A MatchTeamType form
     * @return array
     */
    private function getPlayerList(FormInterface $team)
    {
        return array_map($this->getModelToID(),  $team->get('participants')->getData());
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
