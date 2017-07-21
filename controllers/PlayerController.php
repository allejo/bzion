<?php

use BZIon\Form\Creator\PlayerAdminNotesFormCreator as FormCreator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class PlayerController extends JSONController
{
    private $creator;

    public function showAction(Player $player, Player $me, Request $request)
    {
        if ($me->hasPermission(Permission::VIEW_VISITOR_LOG)) {
            $this->creator = new FormCreator($player);
            $form = $this->creator->create()->handleRequest($request);

            if ($form->isValid()) {
                $form = $this->handleAdminNotesForm($form, $player, $me);
            }

            $formView = $form->createView();
        } else {
            // Don't spend time rendering the form unless we need it
            $formView = null;
        }

        return array(
            "player"         => $player,
            "adminNotesForm" => $formView,
        );
    }

    public function editAction(Player $player, Player $me)
    {
        if (!$me->canEdit($player)) {
            throw new ForbiddenException("You are not allowed to edit other players");
        }

        $params = array(
            'me'   => $player,
            'self' => false,
        );

        return $this->forward('edit', $params, 'Profile');
    }

    public function listAction(Request $request, Player $me, Team $team = null)
    {
        $query = Player::getQueryBuilder();

        // Load all countries into the cache so they are ready for later
        Country::getQueryBuilder()->addToCache();

        if ($team) {
            $query->where('team')->is($team);
        } else {
            // Add all teams to the cache
            $this->getQueryBuilder('Team')
                ->where('members')->greaterThan(0)
                ->addToCache();
        }

        if ($request->query->has('exceptMe')) {
            $query->except($me);
        }

        $groupBy = $request->query->get('groupBy');
        $sortBy = $request->query->get('sortBy');
        $sortOrder = $request->query->get('sortOrder');

        $query
            ->withMatchActivity()
            ->sortBy('name')
            ->active()
        ;

        if ($sortBy || $sortOrder) {
            $sortBy = $sortBy ? $sortBy : 'callsign';
            $sortOrder = $sortOrder ? $sortOrder : 'ASC';

            if ($sortBy === 'activity') {
                $query->sortBy($sortBy);
            }

            if ($sortOrder == 'DESC') {
                $query->reverse();
            }
        }

        $players = $query->getModels($fast = true);

        if ($groupBy) {
            $grouped = [];

            /** @var Player $player */
            foreach ($players as $player) {
                $key = '';

                if ($groupBy == 'country') {
                    $key = $player->getCountry()->getName();
                } elseif ($groupBy == 'team') {
                    $key = $player->getTeam()->getEscapedName();

                    if ($key == '<em>None</em>') {
                        $key = ' ';
                    }
                } elseif ($groupBy == 'activity') {
                    $key = ($player->getMatchActivity() > 0.0) ? 'Active' : 'Inactive';
                }

                $grouped[$key][] = $player;
            }

            ksort($grouped);
            $players = $grouped;
        }

        return array(
            'grouped' => ($groupBy !== null),
            'players' => $players,
        );
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
        return $this->creator->create();
    }
}
