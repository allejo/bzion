<?php

class PageController extends HTMLController {

    public function showDefaultAction() {
        $page = Page::getHomePage();

        if ($page->isValid())
            return $this->showAction(Page::getHomePage());

        $this->drawHeader("Nothing to see here");
        ?>
        <article>
            <h1>Home Page</h1>
            <p>No one has added content to the home page yet!</p>
        </article>
        <?php
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
