<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PlayerController extends JSONController
{
    public function showAction(Player $player)
    {
        return array("player" => $player);
    }

    public function listAction(Request $request, Player $me, Team $team=null)
    {
        if ($startsWith = $request->query->get('startsWith')) {
            $players = Player::getPlayerUsernamesStartingWith($startsWith, $me->getId());
        } elseif ($team) {
            $players = Player::getTeamUsernames($team->getId());
        } else {
            return array("players" => $players);
        }

        if ($this->isJson())
            return new JSONResponse(array('players' => $players));
        else
            $players = Player::arrayIdToModel(array_map(function ($p) {return $p['id'];},$players));

        return array("players" => $players);
    }
}
