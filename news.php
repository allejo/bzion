<?php

include("bzion-load.php");

$header = new Header("News");

$header->draw();

$newsArticles = News::getNews();
?>
<div class="news_content"> 
<?php
foreach ($newsArticles as $key => $id) {
    $news = new News($id);
    echo "<div class=\"news_box\">\n";
    echo "<div class=\"title_box\">\n";
    echo "<div id=\"news_title\">" . $news->getSubject() . "</div>\n";
    $author = new Player($news->getAuthor());
    echo "<div id=\"author\">By " . $author->getUsername() . " at " . $news->getUpdated() . "</div>\n";
    echo "</div>\n";
    echo "<div class=\"news\">". $news->getContent() . "</div>\n";
    echo "</div>\n";
}
?>
</div>
<?php
$footer = new Footer();
$footer->draw();

?>
