<?php

class NewsController extends HTMLController {

    public function listAction() {
        return array("news" => News::getNews());
    }
}
