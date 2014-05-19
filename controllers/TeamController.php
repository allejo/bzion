<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormFactory;

class TeamController extends HTMLController
{
    public function showAction(Team $team)
    {
        return array("team" => $team);
    }

    public function listAction()
    {
        return array("teams" => Team::getTeams());
    }

    public function deleteAction(Team $team, Player $me, Request $request, FormFactory $formFactory)
    {
        if (!$me->hasPermission(Permission::SOFT_DELETE_TEAM)) {
            return new RedirectResponse(Service::getGenerator()->generate('team_list'));
        }

        $form = $formFactory->createBuilder()
            ->add('Yes', 'submit')
            ->add('No', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('Yes')->isClicked()) {
                $team->delete();
                $request->getSession()->getFlashBag()->add('success',
                         "The team {$team->getName()} was deleted successfully");

                return new RedirectResponse(Service::getGenerator()->generate('team_list'));
            }

            return new RedirectResponse($team->getUrl());
        }

        // The form hasn't been submitted, let's render it
        return array('team' => $team, 'form' => $form->createView());
    }
}
