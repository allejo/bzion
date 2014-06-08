<?php

use BZIon\Form\MatchTeamType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\NotBlank;

class MatchController extends CRUDController
{
    public function listByTeamAction(Team $team)
    {
        return $this->render("Match/list.html.twig",
               array ("matches" => $team->getMatches(), "team" => $team));
    }

    public function listByTeamSortAction(Team $team, $type)
    {
        return $this->render("Match/list.html.twig",
            array ("matches" => $team->getMatches($type, 50), "team" => $team));
    }

    public function listAction()
    {
        return array("matches" => Match::getMatches());
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    /**
     * @todo Handle players and the server address
     */
    protected function enter(Form $form, Player $me)
    {
        $firstTeam  = $form->get('first_team');
        $secondTeam = $form->get('second_team');

        $match = Match::enterMatch(
            $firstTeam ->get('team')->getData()->getId(),
            $secondTeam->get('team')->getData()->getId(),
            $firstTeam ->get('score')->getData(),
            $secondTeam->get('score')->getData(),
            $form->get('duration')->getData(),
            $me->getId(),
            $form->get('time')->getData()
        );

        return $match;
    }

    protected function createForm()
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
            ->add('time', 'datetime', array('constraints' => new NotBlank()))
            ->add('enter', 'submit')
            ->getForm();
    }

    protected function validate(Form $form)
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
