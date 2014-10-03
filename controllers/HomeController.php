<?php

class HomeController extends HTMLController
{
    public function showAction()
    {
        // $this->container->get('ladybug')->log($this->getRequest()->getBasePath());

        $matches = MatchController::getQueryBuilder()->sortBy('time')->reverse()->limit(6)->fromPage(1);

        return array("matches" => $matches->getModels(), "news" => News::getNews(0, 5));
    }
}
