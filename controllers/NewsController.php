<?php

class NewsController extends HTMLController
{
    public function showAction(News $article) {
        return array("article" => $article);
    }

    public function listAction() {
        return array("news" => News::getNews());
    }
}
