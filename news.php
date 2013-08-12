<?php

include("bzion-load.php");

$header = new Header("News");

$header->draw();

$newsArticles = News::getNews();

?>

<div class="news"> 
<?php

foreach ($newsArticles as $key => $id) {
    $news = new News($id);
    echo "<div class=\"news_box\">\n";
    echo "<div class=\"news_title_box\">\n";
    echo "<div class=\"news_title\">" . $news->getSubject() . "</div>\n";
    $author = new Player($news->getAuthor());
    echo "</div>\n";
    echo "<div class=\"news_content\">". $news->getContent() . "\n";
    echo "<div class=\"news_author\">By <a href=\"" . $author->getURL() . "\">" . $author->getUsername() . "</a> at " . $news->getUpdated() . "</div></div>\n";
    echo "</div>\n";
}

?>

</div> <!-- end .news -->

<?php

$footer = new Footer();
$footer->draw();

?>
