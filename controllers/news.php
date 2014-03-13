<?php

$header = new Header("News");
$header->draw();

$newsArticles = News::getNews();

foreach ($newsArticles as $key => $id)
{
    $news = new News($id);
    $author = $news->getAuthor();

    echo '<article>';
    echo '    <h1>' . $news->getSubject() . '</h1>';
    echo '    <p>' . $news->getContent() . '</p>';
    echo '    <footer>';
    echo '        Posted by <a href="' . $author->getURL() . '">' . $author->getUsername() . '</a> ' . $news->getUpdated();
    echo '    </footer>';
    echo '</article>';
}

$footer = new Footer();
$footer->draw();
