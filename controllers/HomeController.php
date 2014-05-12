<?php

class HomeController extends HTMLController {

    public function showAction()
    {
        return array("matches" => Match::getMatches(0, 6), "news" => News::getNews());
    }
}
