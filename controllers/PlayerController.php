<?php

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PlayerController extends JSONController
{
    public function showAction(Player $player, Player $me, Request $request)
    {
	if ($me->hasPermission(Permission::VIEW_VISITOR_LOG)) {
	    $form = $this->getAdminNotesForm($player)->handleRequest($request);
	    $form = $this->handleAdminNotesForm($form, $player, $me);
	    $formView = $form->createView();
	} else {
	    // Don't spend time rendering the form unless we need it
	    $formView = null;
	}

        return array(
	  "player" => $player,
	  "adminNotesForm" => $formView,
	);
    }

    public function listAction(Request $request, Player $me, Team $team=null)
    {
        $query = Player::getQueryBuilder()->active();

        if ($startsWith = $request->query->get('startsWith')) {
            $query->where('username')->startsWith($startsWith);
        }

        if ($team) {
            $query->where('team')->is($team);
        }

        if ($request->query->has('exceptMe')) {
            $query->except($me);
        }

        $query->sortBy('username');

        if ($this->isJson())
            return new JSONResponse(array('players' => $query->getArray('username')));
        else
            return array('players' => $query->getModels());
    }

    /**
     * Get the form for admins to secretly gossip about the player
     * @param Player $player The player in question
     * @return Form
     */
    private function getAdminNotesForm($player)
    {
	return Service::getFormFactory()->createBuilder()
            ->add('notes', 'textarea', array(
		'data'     => $player->getAdminNotes(),
                'required' => false,
	    ))
	    ->add('save_and_sign', 'submit', array(
		'label' => 'Save & Sign',
	    ))
            ->add('save', 'submit')
            ->getForm();
    }

    /**
     * Handle the admin notes form
     * @param  Form   $form   The form
     * @param  Player $player The player in question
     * @param  Player $me     The currently logged in player
     * @return Form   The updated form
     */
    private function handleAdminNotesForm($form, $player, $me)
    {
	$notes = $form->get('notes')->getData();
	if ($form->get('save_and_sign')->isClicked()) {
	    $notes .= ' — ' . $me->getUsername() . ' on ' . TimeDate::now()->toRFC2822String();
	}

	$player->setAdminNotes($notes);
	$this->getFlashBag()->add('success', "The admin notes for {$player->getUsername()} have been updated");

	// Reset the form so that the user sees the updated admin notes
	return $this->getAdminNotesForm($player);
    }
}
