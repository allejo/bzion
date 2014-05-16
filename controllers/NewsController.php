<?php

class NewsController extends HTMLController
{
    public function showAction(News $article)
    {
        return array("article" => $article, "categories" => NewsCategory::getCategories());
    }

    public function listAction(NewsCategory $category = null)
    {
        if ($category)
            $news = $category->getNews();
        else
            $news = News::getNews();

        return array("news" => $news, "categories" => NewsCategory::getCategories(), "category" => $category);
    }
}
