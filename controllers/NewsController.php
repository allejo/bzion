<?php

class NewsController extends HTMLController
{
    public function showAction(News $article) {
        return array("article" => $article, "categories" => NewsCategory::getCategories());
    }

    public function listAction() {
        return array("news" => News::getNews(), "categories" => NewsCategory::getCategories());
    }
}
