<?php

class PageController extends HTMLController {

    public function showDefaultAction() {
        return $this->showAction(Page::getHomePage());
    }

    /**
     * @todo Proper 404 page
     */
    public function showAction(Page $page) {
        if (!$page->isValid())
            Header::go("home");

        $this->drawHeader($page->getName());
    ?>

        <article>
            <h1><?= $page->getName(); ?></h1>
            <p><?= $page->getContent(); ?></p>
        </article>

    <?php

    }
}
