<?php

class NewsController extends CRUDController
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

    protected function update($form, $article, $me)
    {
        $article->updateCategory($form->get('category')->getData()->getId())
                ->updateSubject($form->get('subject')->getData())
                ->updateContent($form->get('content')->getData())
                ->updateStatus($form->get('status')->getData())
                ->updateLastEditor($me->getId())
                ->updateEditTimestamp();

        return $article;
    }

    protected function enter($form, $me)
    {
        return News::addNews(
            $form->get('subject')->getData(),
            $form->get('content')->getData(),
            $me->getId(),
            $form->get('category')->getData()->getId(),
            $form->get('status')->getData()
        );
    }
}
