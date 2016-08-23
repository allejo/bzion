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
        $qb = $this->getQueryBuilder();

        $currentPage = $request->query->get('page', 1);

        $visits = $qb->sortBy('timestamp')->reverse()
            ->limit(30)->fromPage($currentPage)
            ->getModels();

        return array(
            "visits"      => $visits,
            "currentPage" => $currentPage,
            "totalPages"  => $qb->countPages()
        );
    }

    public static function getQueryBuilder($type = "Visit") {
        return $type::getQueryBuilder();
    }
}
