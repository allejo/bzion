<?php

$header = new Header("Home");
$header->draw();

$page = Page::getHomePage();

?>

<article>
    <h1><?= $page->getName(); ?></h1>
    <p><?= $page->getContent(); ?></p>
</article>

<?php

$footer = new Footer();
$footer->draw();

?>
