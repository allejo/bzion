<?php

use Symfony\Component\HttpFoundation\RedirectResponse;

class PageController extends CRUDController
{
    public function showDefaultAction()
    {
        $page = Page::getHomePage();

        if ($page->isValid())
            return $this->forward("show", array("page" => $page));

        return $this->render("Page/default.html.twig");
    }

    public function showAction(Page $page)
    {
        return array("page" => $page);
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function editAction(Player $me, Page $page)
    {
        return $this->edit($page, $me, "page");
    }

    public function deleteAction(Player $me, Page $page)
    {
        return $this->delete($page, $me);
    }

    protected function update($form, $page, $me)
    {
        $page->setName($form->get('name')->getData())
             ->setContent($form->get('content')->getData())
             ->setStatus($form->get('status')->getData())
             ->updateEditTimestamp();

        return $page;
    }

    protected function enter($form, $me)
    {
        return Page::addPage(
            $form->get('name')->getData(),
            $form->get('content')->getData(),
            $me->getId(),
            $form->get('status')->getData()
        );
    }

    protected function redirectToList($model)
    {
        return new RedirectResponse(
            Service::getGenerator()->generate("index")
        );
    }
}
