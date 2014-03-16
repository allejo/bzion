<?php

abstract class HTMLController extends Controller {

    /**
     * Shows the HTML page header
     * @param string $title The `<title>` of the page to show
     */
    public function drawHeader($title) {
        $header = new Header($title);
        $header->draw();
    }

    /**
     * Shows the HTML page footer
     *
     * {@inheritDoc}
     */
    public function cleanup() {
        $footer = new Footer();
        $footer->draw();
    }
}
