<?php

class AdminController extends HTMLController
{
    public function listAction()
    {
        // @TODO Make a function to display actual admins
        return array("admins" => Player::getPlayers());
    }

    public function wipeAction(Player $me)
    {
        $wipeable = array('Ban', 'Match', 'News', 'NewsCategory', 'Page', 'Player', 'Server', 'Team');
        $models   = array();

        foreach ($wipeable as $type) {
            if (!$me->hasPermission($type::getHardDeletePermission())) {
                continue;
            }

            $models = array_merge($models, $type::getQueryBuilder()
                ->where('status')->equals('deleted')
                ->getModels());
        }

        return array('models' => $models);
    }
}
