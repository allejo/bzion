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
        $qb = Visit::getQueryBuilder();

        $currentPage = $this->getCurrentPage();

        if ($request->query->has('search')) {
            $qb->search($request->query->get('search'));
        }

        $visits = $qb
            ->orderBy('timestamp', 'DESC')
            ->limit(30)->fromPage($currentPage)
            ->getModels()
        ;

        return [
            'visits'      => $visits,
            'currentPage' => $currentPage,
            'totalPages'  => $qb->countPages(),
            'search'      => $request->query->get('search')
        ];
    }
}
