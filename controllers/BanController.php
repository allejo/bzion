<?php

class BanController extends HTMLController {

    public function listAction() {
        return array("bans" => Ban::getBans());
    }
}
