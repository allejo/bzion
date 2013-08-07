<?php

include("bzion-load.php");

$header = new Header();

if (!isset($_GET['alias'])) {
    $header->go("home");
}

$page = Page::getFromAlias($_GET['alias']);

$header->draw($page->getName());

echo $page->getContent();

$footer = new Footer();
$footer->draw();

?>
