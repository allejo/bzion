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
    echo "<div class='news_box'>"
    echo "<div class='title_box'>"
    echo "<div id='news_title'>" . $news->getSubject() . "</div>";
    $author = new Player($news->getAuthor());
    echo "<div id='author'>By " . $author->getUsername() . " at " . $news->getUpdated() . "</div>";
    echo "</div>"
    echo "<div class='news'>". $news->getContent() . "</div>";
    echo "</div>"
}
?>
</div>
<?php
$footer = new Footer();
$footer->draw();

?>
