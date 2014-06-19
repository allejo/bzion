<?php

use BZIon\Form\ModelType;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

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

    protected function fill($form, $article)
    {
        $form->get('category')->setData($article->getCategory());
        $form->get('subject')->setData($article->getSubject());
        $form->get('content')->setData($article->getContent());
        $form->get('status')->setData($article->getStatus());
    }

    protected function update($form, $article, $me)
    {
        $article->updateCategory($form->get('category')->getData()->getId());
        $article->updateSubject($form->get('subject')->getData());
        $article->updateContent($form->get('content')->getData());
        $article->updateStatus($form->get('status')->getData());
        $article->updateLastEditor($me->getId());
        $article->updateEditTimestamp();

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

    public function createForm()
    {
        return Service::getFormFactory()->createBuilder()
            ->add('category', new ModelType('NewsCategory'))
            ->add('subject', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 100,
                    )),
                ),
            ))
            ->add('content', 'textarea', array(
                'constraints' => new NotBlank()
            ))
            ->add('status', 'choice', array(
                'choices' => array(
                    'published' => 'Public',
                    'revision' => 'Revision',
                    'draft' => 'Draft',
                ),
            ))
            ->add('enter', 'submit')
            ->getForm();
    }
}
