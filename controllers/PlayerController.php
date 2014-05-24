<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PlayerController extends JSONController
{
    public function showAction(Player $player)
    {
        return array("player" => $player);
    }

    public function listAction(Request $request, Player $me)
    {
        if ($startsWith = $request->query->get('startsWith')) {
            $players = Player::getPlayerUsernamesStartingWith($startsWith, $me->getId());

            if ($this->isJson())
                return new JSONResponse(array('players' => $players));
            else
                $players = Player::arrayIdToModel(array_map(function($p){return $p['id'];},$players));
        } else {
            $players = Player::getPlayers();
        }

        return array("players" => $players);
    }
}
