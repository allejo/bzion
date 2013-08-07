<?php

include("bzion-load.php");

$header = new Header();

if (isset($_GET['alias'])) {
    $page = Page::getFromAlias($_GET['alias']);
} else if (isset($_GET['id'])) {
    $page = new Page($_GET['id']);
} else {
    $header->go("home");
}

$header->draw($page->getName());

echo $page->getContent();

$footer = new Footer();
$footer->draw();

?>
