<?php

use Symfony\Component\HttpFoundation\Request;

class NewsController extends CRUDController
{
    public function showAction(News $article)
    {
        return array("article" => $article, "categories" => $this->getCategories());
    }

    public function listAction(Request $request, NewsCategory $category = null)
    {
        $qb = $this->getQueryBuilder();

        $currentPage = $request->query->get('page', 1);

        $news = $qb->sortBy('created')->reverse()
            ->where('category')->is($category)
            ->limit(5)->fromPage($request->query->get('page', 1))
            ->getModels();

        return array(
            "news"        => $news,
            "categories"  => $this->getCategories(),
            "category"    => $category,
            "currentPage" => $currentPage,
            "totalPages"  => $qb->countPages()
        );
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
