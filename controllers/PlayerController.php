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
        $query = Player::getQueryBuilder()->active();

        if ($startsWith = $request->query->get('startsWith')) {
            $query->where('username')->startsWith($startsWith);
        }

        if ($team) {
            $query->where('team')->is($team);
        }

        if ($this->isJson())
            return new JSONResponse(array('players' => $query->getArray('username')));
        else
            return array('players' => $query->getModels());
    }
}
