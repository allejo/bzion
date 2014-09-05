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

    private function getCategories()
    {
        return $this->getQueryBuilder('NewsCategory')
            ->sortBy('name')
            ->getModels();
    }
}
