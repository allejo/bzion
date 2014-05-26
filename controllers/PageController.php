<?php

class PageController extends HTMLController
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
}
