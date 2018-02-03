<?php

use Symfony\Component\HttpFoundation\Request;

class NewsController extends CRUDController
{
    public function showAction(Player $me, News $article)
    {
        if ($article->isDraft() && (!$me->isValid() || !$me->hasPermission(News::EDIT_PERMISSION))) {
            throw new ForbiddenException('You do not have permission to view draft posts.');
        }

        return [
            'article' => $article,
            'categories' => $this->getCategories(),
        ];
    }

    public function listAction(Request $request, Player $me, NewsCategory $category = null)
    {
        $currentPage = $this->getCurrentPage();
        $qb = $this->getQueryBuilder();

        $news = $qb
            ->orderBy('created', 'DESC')
            ->limit(5)
            ->fromPage($currentPage)
        ;

        if ($category !== null) {
            $news->where('category', '=', $category->getId());
        }

        if (!$me->isValid() || !$me->hasPermission(News::CREATE_PERMISSION)) {
            $news->whereNot('is_draft', '=', true);
        }

        return [
            'news'        => $news->getModels(true),
            'categories'  => $this->getCategories(),
            'category'    => $category,
            'currentPage' => $currentPage,
            'totalPages'  => $qb->countPages(),
        ];
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
