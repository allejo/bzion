<?php

use BZIon\Form\MatchTeamType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class MatchController extends HTMLController
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

    public function createAction(Player $me, Request $request)
    {
        if (!$me->hasPermission(Permission::ENTER_MATCH))
            throw new ForbiddenException("You are not allowed to report matches");

        $form = $this->createForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->assertDifferentTeams($form);
            if ($form->isValid()) {
                //... do stuff
            }
        }


        return array("form" => $form->createView());
    }

    private function createForm()
    {
        return Service::getFormFactory()->createBuilder()
            ->add('first_team', new MatchTeamType())
            ->add('second_team', new MatchTeamType())
            ->add('match_duration', 'choice', array(
                'choices' => array_keys(unserialize(DURATION)),
                'expanded' => true
            ))
            ->add('server_address', 'text')
            ->add('time', 'datetime', array(
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('enter', 'submit')
            ->getForm();
    }

    /**
     * Make sure that two different teams participated in a match, i.e. a team
     * didn't match against itself
     * @param Form $form The form to evaluate
     */
    private function assertDifferentTeams(Form $form)
    {
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
