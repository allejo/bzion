<?php

include("bzion-load.php");

$header = new Header("Home");
$header->draw();

$page = Page::getHomePage();

?>

<div class="custom_page">
    <div class="custom_page_title"><?php echo $page->getName(); ?></div>
    <div class="custom_page_content"><?php echo $page->getContent(); ?></div>
</div>

<?php

$footer = new Footer();
$footer->draw();

?>
