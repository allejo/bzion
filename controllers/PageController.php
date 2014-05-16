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

    /**
     * @todo Proper 404 page
     */
    public function showAction(Page $page)
    {
        if (!$page->isValid())
            Header::go("home");
        else return array("page" => $page);
    }
}
