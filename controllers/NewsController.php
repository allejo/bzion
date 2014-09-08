<?php

class NewsController extends CRUDController
{
    public function showAction(News $article)
    {
        return array("article" => $article, "categories" => $this->getCategories());
    }

    public function listAction(NewsCategory $category = null)
    {
        $news = $this->getQueryBuilder()
            ->sortBy('created')->reverse()
            ->where('category')->is($category)
            ->getModels();

        return array("news" => $news, "categories" => $this->getCategories(), "category" => $category);
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function editAction(Player $me, News $article)
    {
        return $this->edit($article, $me, "article");
    }

    public function deleteAction(Player $me, News $article)
    {
        return $this->delete($article, $me);
    }

    private function getCategories()
    {
        return $this->getQueryBuilder('NewsCategory')
            ->sortBy('name')
            ->getModels();
    }
}
