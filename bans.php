<?php

include("bzion-load.php");

$header = new Header("Bans");

$header->draw();

$banList = Ban::getBans();

foreach ($banList as $key => $id) {
    $ban = new Ban($id);
    $bannedPlayer = new Player($ban->getPlayer());
    echo "<h4>" . $bannedPlayer->getUsername() . "</h4>";
    $author = new Player($ban->getAuthor());
    echo "<small>By " . $author->getUsername() . " at " . $ban->getUpdated() . "</small><br />";
    echo $ban->getReason() . "<br />";
    echo "<br />";
}

$footer = new Footer();
$footer->draw();

?>
