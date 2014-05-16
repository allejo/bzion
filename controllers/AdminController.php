<?php

class AdminController extends HTMLController
{
    public function listAction()
    {
        // @TODO Make a function to display actual admins
        return array("admins" => Player::getPlayers());
    }
}
