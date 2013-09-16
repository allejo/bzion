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

?>

<div class="custom_page">
    <div class="custom_page_title"><?php echo $page->getName(); ?></div>
    <div class="custom_page_content"><?php echo $page->getContent(); ?></div>
</div>

<?php

$footer = new Footer();
$footer->draw();

?>
