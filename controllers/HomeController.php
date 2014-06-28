<?php

class HomeController extends HTMLController
{
    public function showAction()
    {
        $matches = Match::getQueryBuilder()->active()->sortBy('time')->reverse()->limit(6)->fromPage(1);

        return array("matches" => $matches->getModels(), "news" => News::getNews(0, 5));
    }
}
