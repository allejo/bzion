<?php

use Symfony\Component\HttpFoundation\RedirectResponse;

class RoleController extends CRUDController
{
    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function editAction(Player $me, Role $role)
    {
        return $this->edit($role, $me, "role");
    }

    protected function redirectTo($model)
    {
        // Redirect to the server list after creating/editing a role
        return new RedirectResponse(Service::getGenerator()->generate('admin_landing'));
    }
}
