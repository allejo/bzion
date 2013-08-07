<?php

include("bzion-load.php");

$header = new Header();


if (isset($_GET['alias'])) {
    $player = Player::getFromAlias($_GET['alias']);
} else if (isset($_GET['id'])) {
    $player = new Player($_GET['id']);
}

if (isset($player)) {
    $header->draw("Players :: " . $player->getUsername());

    echo "<b>" . $player->getUsername() . "</b><br />";
    echo "Team: " . $player->getTeam() . "<br />";
    echo "Joined: " . $player->getJoinedDate() . "<br />";

} else {
    $header->draw("Players");

    $players = Player::getPlayers();

    foreach ($players as $key => $bzid) {
        $player = new Player($bzid);
        echo "<b><a href='" . $player->getURL() . "'>" . $player->getUsername() . "</a></b><br />";
        echo "Team: " . $player->getTeam() . "<br />";
        echo "Joined: " . $player->getJoinedDate() . "<br />";

        echo "<br />";
    }

}

$footer = new Footer();
$footer->draw();

?>
