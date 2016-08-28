<?php

use Symfony\Component\HttpFoundation\Request;

class VisitLogController extends HTMLController
{
    public function setup()
    {
        if (!$this->getMe()->hasPermission(Permission::VIEW_VISITOR_LOG)) {
            throw new ForbiddenException("You are not allowed to view visitor logs.");
        }
    }

    public function listAction(Request $request)
    {
        /** @var VisitQueryBuilder $qb */
        $qb = $this->getQueryBuilder();

        $currentPage = $request->query->get('page', 1);

        if ($request->query->has('search')) {
            $qb->search($request->query->get('search'));
        }

        $visits = $qb->sortBy('timestamp')->reverse()
            ->limit(30)->fromPage($currentPage)
            ->getModels($fast = true);

        return array(
            "visits"      => $visits,
            "currentPage" => $currentPage,
            "totalPages"  => $qb->countPages(),
            "search"      => $request->query->get('search')
        );
    }

    public static function getQueryBuilder($type = "Visit") {
        return $type::getQueryBuilder();
    }
}
