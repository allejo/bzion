<?php

use BZIon\Form\MatchTeamType;
use BZIon\Form\DatetimeWithTimezoneType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\LessThan;

class MatchController extends CRUDController
{
    public function listAction(Request $request, Team $team=null, $type=null)
    {
        $query = Match::getQueryBuilder()->active()
               ->sortBy('time')->reverse()
               ->with($team, $type)
               ->limit(50)->fromPage($request->query->get('page', 1));

        return array("matches" => $query->getModels(), "team" => $team);
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    /**
     * @todo Handle players and the server address
     */
    protected function enter($form, $me)
    {
        $firstTeam  = $form->get('first_team');
        $secondTeam = $form->get('second_team');

        $firstPlayers  = array_map($this->getModelToID(),  $firstTeam->get('participants')->getData());
        $secondPlayers = array_map($this->getModelToID(), $secondTeam->get('participants')->getData());

        $serverInfo = explode(':', $form->get('server_address')->getData());
        if (!isset($serverInfo[1])) {
            $serverInfo[1] = 5154;
        }

        $match = Match::enterMatch(
            $firstTeam ->get('team')->getData()->getId(),
            $secondTeam->get('team')->getData()->getId(),
            $firstTeam ->get('score')->getData(),
            $secondTeam->get('score')->getData(),
            $form->get('duration')->getData(),
            $me->getId(),
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

    public function createForm()
    {
        return Service::getFormFactory()->createBuilder()
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
                        'value'   => TimeDate::now()->addMinutes(10),
                        'message' => 'The timestamp of the match must not be in the future'
                    ))
                ),
                'data' => TimeDate::now()
            ))
            ->add('enter', 'submit')
            ->getForm();
    }

    protected function validate($form)
    {
        // Make sure that two different teams participated in a match, i.e. a team
        // didn't match against itself
        $firstTeam  = $form->get('first_team')->get('team')->getData();
        $secondTeam = $form->get('second_team')->get('team')->getData();

        if (!$firstTeam || !$secondTeam)
            return;

        if ($firstTeam->getId() == $secondTeam->getId()) {
            $message = "You can't report a match where a team played against itself!";
            $form->addError(new FormError($message));
        }
    }
}
