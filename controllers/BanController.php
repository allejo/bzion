<?php

class BanController extends HTMLController
{
    public function showAction(Ban $ban) {
        return array("ban" => $ban);
    }

    public function listAction() {
        return array("bans" => Ban::getBans());
    }
}
