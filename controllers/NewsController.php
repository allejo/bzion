<?php

class NewsController extends HTMLController
{
    public function showAction($id) {
        return array("article" => new News($id));
    }

    public function listAction() {
        return array("news" => News::getNews());
    }
}
