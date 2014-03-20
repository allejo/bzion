<?php

class PlayerController extends HTMLController {

    public function showAction(Player $player) {
        if ($this->validate($player)) {
            return array("player" => $player);
        }
    }

    public function listAction() {
        return array("players" => Player::getPlayers());
    }
}
