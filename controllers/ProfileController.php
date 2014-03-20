<?php

use Symfony\Component\HttpFoundation\Session\Session;

class ProfileController extends HTMLController {
    private $me;

    public function setup() {
        $session = $this->getRequest()->getSession();

        if (!$session->has("username")) {
            $header->go("home");
        }

        $this->me = new Player($session->get("playerId"));
    }

    public function cleanup() {
        $footer = new Footer();

        $footer->addScript("assets/js/profile.js");
        $footer->draw();
    }

    public function editAction() {
        return array("player" => $this->me, "countries" => Country::getCountries());
    }

    public function showAction() {
        return array("player" => $this->me);
    }
}
