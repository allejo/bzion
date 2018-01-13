<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * @todo Configure the AdminController to be behind a Symfony firewall
 */
class AdminController extends HTMLController
{
    private static $wipeableModels = ['Ban', 'Map', 'Match', 'News', 'NewsCategory', 'Page', 'Server', 'Team'];

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
        if (!$me->isValid()) {
            throw new ForbiddenException('Please log in to view this page.');
        }

        // @todo Model editing should be a generic permission
        $canViewModelEditor = true;
        $canViewPageEditor = $this->isEditorFor(Page::class, $me);
        $canViewRoleEditor = $this->isEditorFor(Role::class, $me);
        $canViewVisitLog   = $me->hasPermission(Permission::VIEW_VISITOR_LOG);

        if (!$canViewPageEditor && !$canViewRoleEditor && !$canViewVisitLog) {
            throw new ForbiddenException('Contact a site administrator if you feel you should have access to this page.');
        }

        return [
            'canViewPageEditor' => $canViewPageEditor,
            'canViewRoleEditor' => $canViewRoleEditor,
            'canViewModelEditor' => $canViewModelEditor,
            'canViewVisitLog' => $canViewVisitLog,
        ];
    }

    public function pageListAction(Player $me)
    {
        if (!$me->isValid()) {
            throw new ForbiddenException('Please log in to view this page.');
        }

        if (!$this->isEditorFor(Page::class, $me)) {
            throw new ForbiddenException('Contact a site administrator if you feel you should have access to this page.');
        }

        $pages = Page::getQueryBuilder()
            ->where('status')->notEquals('deleted')
            ->getModels(true)
        ;

        return [
            'pages' => $pages,
            'canCreate' => $me->hasPermission(Page::CREATE_PERMISSION),
            'canEdit' => $me->hasPermission(Page::EDIT_PERMISSION),
            'canDelete' => $me->hasPermission(Page::SOFT_DELETE_PERMISSION),
            'canWipe' => $me->hasPermission(Page::HARD_DELETE_PERMISSION),
        ];
    }

    public function roleListAction(Player $me)
    {
        if (!$me->isValid()) {
            throw new ForbiddenException('Please log in to view this page.');
        }

        if (!$this->isEditorFor(Role::class, $me)) {
            throw new ForbiddenException('Contact a site administrator if you feel you should have access to this page.');
        }

        $roles = Role::getQueryBuilder()
            ->sortBy('display_order')
            ->getModels($fast = true)
        ;

        return [
            'roles' => $roles,
            'canCreate' => $me->hasPermission(Role::CREATE_PERMISSION),
            'canEdit' => $me->hasPermission(Role::EDIT_PERMISSION),
            'canDelete' => $me->hasPermission(Role::SOFT_DELETE_PERMISSION),
            'canWipe' => $me->hasPermission(Role::HARD_DELETE_PERMISSION),
        ];
    }

    public function modelsAction(Player $me)
    {
        if (!$me->isValid()) {
            throw new ForbiddenException('Please log in to view this page.');
        }

        // @todo Implement a new and proper "Model Editor" permission
        if (!$me->hasPermission(Team::SOFT_DELETE_PERMISSION)) {
            throw new ForbiddenException('Contact a site administrator if you feel you should have access to this page.');
        }

        return [

        ];
    }

    public function modelListAction(Request $request, Player $me, $type)
    {
        $type = ucfirst($type);

        if (!$me->isValid()) {
            throw new ForbiddenException('Please log in to view this page.');
        }

        if (!$me->hasPermission($type::SOFT_DELETE_PERMISSION)) {
            throw new ForbiddenException('Contact a site administrator if you feel you should have access to this page.');
        }

        $searchTerm = $request->get('search');

        $currentPage = $this->getCurrentPage();

        /** @var QueryBuilder $qb */
        $qb = $type::getQueryBuilder()
            ->where('status')->equals('deleted')
            ->sortBy('name')
        ;

        if ($searchTerm !== null) {
            $qb->where('name')->isLike($searchTerm);
        }

        $models = $qb
            ->limit(15)
            ->fromPage($currentPage)
            ->getModels()
        ;

        return [
            'type' => $type,
            'models' => $models,
            'canRestore' => $me->hasPermission($type::SOFT_DELETE_PERMISSION),
            'canWipe' => $me->hasPermission($type::HARD_DELETE_PERMISSION),
            'currentPage' => $currentPage,
            'totalPages' => $qb->countPages(),
            'searchTerm' => $searchTerm,
        ];
    }

    public function wipeAction(Player $me)
    {
        $canViewThisPage = false;
        $wipeable = array('Ban', 'Map', 'Match', 'News', 'NewsCategory', 'Page', 'Server', 'Team');
        $models   = array();

        foreach ($wipeable as $type) {
            if (!$me->hasPermission($type::HARD_DELETE_PERMISSION)) {
                continue;
            }

            $canViewThisPage = true;
            $models = array_merge($models, $type::getQueryBuilder()
                ->where('status')->equals('deleted')
                ->getModels());
        }

        // Permission checking
        if (!$me->isValid()) {
            throw new ForbiddenException("Please log in to view this page.");
        }
        if (!$canViewThisPage) {
            throw new ForbiddenException("Contact a site administrator if you feel you should have access to this page.");
        }

        return array('models' => $models);
    }

    private function isEditorFor($className, Player $me)
    {
        $permissionConstants = [
            'CREATE_PERMISSION',
            'EDIT_PERMISSION',
            'SOFT_DELETE_PERMISSION',
            'HARD_DELETE_PERMISSION',
        ];

        $reflector = new ReflectionClass($className);

        foreach ($permissionConstants as $permission) {
            $permissionName = $reflector->getConstant($permission);

            if ($me->hasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }
}
