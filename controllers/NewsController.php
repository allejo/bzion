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

    public function deleteAction(Player $me, News $article)
    {
        return $this->delete($article, $me);
    }

    protected function enter(Form $form, Player $me)
    {
        return News::addNews(
            $form->get('subject')->getData(),
            $form->get('content')->getData(),
            $me->getId(),
            $form->get('category')->getData()->getId()
        );
    }

    protected function createForm()
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
            ->add('enter', 'submit')
            ->getForm();
    }
}
