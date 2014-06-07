<?php

use BZIon\Form\MatchTeamType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\NotBlank;

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

    public function createAction(Player $me, Request $request, Session $session)
    {
        if (!$me->hasPermission(Match::getCreatePermission()))
            throw new ForbiddenException("You are not allowed to report matches");

        $form = $this->createForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->assertDifferentTeams($form);
            if ($form->isValid()) {
                $this->enter($form, $me);
                $session->getFlashBag()->add("success", "The match has been successfully reported");

                return new RedirectResponse($this->generate('match_list'));
            }
        }

        return array("form" => $form->createView());
    }

    /**
     * Enter a new match into the database
     * @todo Handle players and the server address
     * @param  Form   $form The form to get the data from
     * @param  Player $me   The player who enters the match
     * @return Match  The new match
     */
    private function enter(Form $form, Player $me)
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

    private function createForm()
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

    /**
     * Make sure that two different teams participated in a match, i.e. a team
     * didn't match against itself
     * @param  Form $form The form to evaluate
     * @return void
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
