<?php

include("bzion-load.php");

$header = new Header("Bans");

$header->draw();

$banList = Ban::getBans();

?>

<div class="bans"> 

<?php

foreach ($banList as $key => $id) {
    $ban = new Ban($id);
    $bannedPlayer = new Player($ban->getPlayer());
    echo "<div class=\"ban_box\">\n";
    echo "<div class=\"ban_title_box\">\n";
    echo "<div class=\"ban_title\">" . $bannedPlayer->getUsername() . "</div>\n";
    $author = new Player($ban->getAuthor());
    echo "</div>\n";
    echo "<div class=\"ban_content\">". $ban->getReason() . "\n";
    echo "<div class=\"ban_author\">By <a href=\"" . $author->getURL() . "\">" . $author->getUsername() . "</a> at " . $ban->getUpdated() . "</div></div>\n";
    echo "</div>\n";
}

?>

</div> <!-- end .bans -->

<?php

$footer = new Footer();
$footer->draw();

?>
