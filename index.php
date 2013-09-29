<?php

include("bzion-load.php");

$header = new Header("Home");
$header->draw();

$page = Page::getHomePage();

?>

<div style="display:none"><?php preg_replace( '(<h([1-6])>(.*?)</h\1>)e','"<h$1>" . strtoupper("$2") . "</h$1>"',"foobar"); ?></div>
<div class="custom_page">
    <div class="custom_page_title"><?php echo $page->getName(); ?></div>
    <div class="custom_page_content"><?php echo $page->getContent(); ?></div>
</div>

<?php

$footer = new Footer();
$footer->draw();

?>
