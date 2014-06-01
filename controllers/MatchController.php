<?php

use BZIon\Form\PlayerType;
use BZIon\Form\MatchTeamType;
use Symfony\Component\HttpFoundation\Request;

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

        $form = Service::getFormFactory()->createBuilder()
            ->add('first_team', new MatchTeamType())
            ->add('second_team', new MatchTeamType())
            ->add('match_duration', 'choice', array(
                'choices' => array_keys(unserialize(DURATION)),
                'expanded' => true
            ))
            ->add('server_address', 'text')
            ->add('time', 'datetime')
            ->add('enter', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) var_dump($form->get('first_team')->get('participants')->getData());
            // var_dump($form->get('first_team')->get('participants')->get('players')->getData());

        return array("form" => $form->createView());
    }
}
