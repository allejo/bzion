<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PageController extends CRUDController
{
    public function showDefaultAction()
    {
        $page = Page::getHomePage();

        if ($page->isValid()) {
            return $this->forward("show", array("page" => $page));
        }

        return $this->render("Page/default.html.twig");
    }

    public function showAction(Page $page)
    {
        return array("page" => $page);
    }

    public function createAction(Player $me, Request $request)
    {
        $this->data->set('name', $request->query->get('name'));

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

    protected function redirectToList($model)
    {
        return new RedirectResponse(
            Service::getGenerator()->generate("index")
        );
    }
}
