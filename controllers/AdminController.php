<?php

class AdminController extends HTMLController
{
    public function listAction()
    {
        $rolesToDisplay = Role::getLeaderRoles();
        $roles = array();

        foreach ($rolesToDisplay as $role)
        {
            $roleMembers = (new Role($role->getId()))->getUsers();

            if (count($roleMembers) > 0)
            {
                $roles[] = array(
                    "role" => $role,
                    "members" => $roleMembers
                );
            }
        }

        return array("role_sections" => $roles);
    }

    public function wipeAction(Player $me)
    {
        $wipeable = array('Ban', 'Match', 'News', 'NewsCategory', 'Page', 'Player', 'Server', 'Team');
        $models   = array();

        foreach ($wipeable as $type) {
            if (!$me->hasPermission($type::HARD_DELETE_PERMISSION)) {
                continue;
            }

            $models = array_merge($models, $type::getQueryBuilder()
                ->where('status')->equals('deleted')
                ->getModels());
        }

        return array('models' => $models);
    }
}
