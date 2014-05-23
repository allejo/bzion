<?php

class ProfileController extends HTMLController
{
    public function setup()
    {
        $this->requireLogin();
    }

    public function editAction(Player $me)
    {
        return array("player" => $me, "countries" => Country::getCountries());
    }

    public function showAction(Player $me)
    {
        return array("player" => $me);
    }
}
