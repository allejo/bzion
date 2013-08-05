<?php

include("bzion-load.php");

$header = new Header("News");

$header->draw();

$newsArticles = News::getNews();

echo "<br /><br />";

foreach ($newsArticles as $key => $id) {
    $news = new News($id);
    echo "<h4>" . $news->getSubject() . "</h4>";
    $author = new Player($news->getAuthor());
    echo "<small>By " . $author->getUsername() . " at " . $news->getUpdated() . "</small><br />";
    echo $news->getContent() . "<br />";
    echo "<br />";
}

$footer = new Footer();
$footer->draw();

?>
