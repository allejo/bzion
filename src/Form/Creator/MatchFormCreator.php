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
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('first_team', MatchTeamType::class, array(
                'disableTeam' => $this->isEdit()
            ))
            ->add('second_team', MatchTeamType::class, array(
                'disableTeam' => $this->isEdit()
            ))
            ->add('duration', ChoiceType::class, array(
                'choices'     => $durations,
                'constraints' => new NotBlank(),
                'expanded'    => true
            ))
            ->add('server_address', TextType::class, array(
                'required' => false,
                'attr'     => array('placeholder' => 'brad.guleague.org:5100'),
            ))
            ->add('time', DatetimeWithTimezoneType::class, array(
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
        $form->get('server_address')->setData($match->getServerAddress());
        $form->get('time')->setData($match->getTimestamp());
        $form->get('map')->setData($match->getMap());
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

        $serverInfo = $this->getServerInfo($form->get('server_address'));

        $match->setTeamPlayers(
            $this->getPlayerList($firstTeam),
            $this->getPlayerList($secondTeam)
        );

        $match->setTeamPoints(
            $firstTeam->get('score')->getData(),
            $secondTeam->get('score')->getData()
        );

        $match->setDuration($form->get('duration')->getData())
            ->setServerAddress($serverInfo[0], $serverInfo[1])
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

        $serverInfo = $this->getServerInfo($form->get('server_address'));

        $match = \Match::enterMatch(
            $firstTeam->get('team')->getData()->getId(),
            $secondTeam->get('team')->getData()->getId(),
            $firstTeam->get('score')->getData(),
            $secondTeam->get('score')->getData(),
            $form->get('duration')->getData(),
            $this->me->getId(),
            $form->get('time')->getData(),
            $this->getPlayerList($firstTeam),
            $this->getPlayerList($secondTeam),
            $serverInfo[0],
            $serverInfo[1],
            null,
            $form->get('map')->getData()->getId()
        );

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
     * Get the server address and port of a match
     *
     * @param FormInterface $server A text form representing the server
     * @return array
     */
    private function getServerInfo(FormInterface $server)
    {
        $serverInfo = explode(':', $server->getData());
        if (!isset($serverInfo[1])) {
            $serverInfo[1] = 5154;
        }

        return $serverInfo;
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
