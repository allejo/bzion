<?php

class NewsController extends HTMLController {

    public function listAction() {
        $this->drawHeader("News");
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
    }
}
