<?php

abstract class HTMLController extends Controller {

    /**
     * Shows the HTML page header
     *
     * {@inheritDoc}
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
