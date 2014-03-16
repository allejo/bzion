<?php

class BanController extends HTMLController {

    public function listAction() {
        $this->drawHeader("Bans");

        $banList = Ban::getBans();

        foreach ($banList as $key => $id)
        {
            $ban = new Ban($id);
            $bannedPlayer = $ban->getPlayer();
            $author = $ban->getAuthor();

            echo '<article>';
            echo '    <h1>' . $bannedPlayer->getUsername() . '</h1>';
            echo '    <p>' . $ban->getReason() . '</p>';
            echo '    <footer>';
            echo '        Posted by <a href="' . $author->getURL() . '">' . $author->getUsername() . '</a> ' . $ban->getUpdated();
            echo '    </footer>';
            echo '</article>';
        }
    }


}
