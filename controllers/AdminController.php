<?php


class AdminController extends HTMLController
{
    public function listAction()
    {
        $rolesToDisplay = Role::getLeaderRoles();
        $roles = array();

        foreach ($rolesToDisplay as $role) {
            $roleMembers = $role->getUsers();

            if (count($roleMembers) > 0) {
                $roles[] = array(
                    "role"    => $role,
                    "members" => $roleMembers
                );
            }
        }

        return array("role_sections" => $roles);
    }

    public function landingAction(Player $me)
    {
        $pages = $roles = null;

        if (
            $me->hasPermission(Permission::SOFT_DELETE_PAGE)
            || $me->hasPermission(Permission::EDIT_PAGE)
        ) {
            $pages = Page::getQueryBuilder()
                ->where('status')->notEquals('active')
                ->where('status')->notEquals('deleted')
                ->getModels($fast = true);
        }

        if (
            $me->hasPermission(Permission::CREATE_ROLE)
            || $me->hasPermission(Permission::EDIT_ROLE)
            || $me->hasPermission(Permission::HARD_DELETE_ROLE)
        ) {
            $roles = Role::getQueryBuilder()
                ->sortBy('display_order')
                ->getModels($fast = true);
        }

        return array(
            'pages' => $pages,
            'roles' => $roles
        );
    }

    public function wipeAction(Player $me)
    {
        $wipeable = array('Ban', 'Map', 'Match', 'News', 'NewsCategory', 'Page', 'Server', 'Team');
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
