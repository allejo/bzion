<?php

include("bzion-load.php");

$header = new Header();


if (isset($_GET['alias'])) {
    $player = Player::getFromAlias($_GET['alias']);
} else if (isset($_GET['id'])) {
    $player = new Player($_GET['id']);
}

if (isset($player)) {
    if ($player->isValid()) {
        $header->draw("Players :: " . $player->getUsername());

        $playerTeam = $player->getTeam();
        $teamlink = $playerTeam->getName();

        if ($playerTeam->isValid()) {
            $teamlink = '<a href="' . $playerTeam->getURL() . '">' . $teamlink . '</a>';
        }

        echo "<b>" . $player->getUsername() . "</b><br />";
        echo "Team: $teamlink<br />";
        echo "Joined: " . $player->getJoinedDate() . "<br />";
    } else {
        $header->draw("Players");

        echo "The specified player could not be found. <br />";
    }

} else {
    $header->draw("Players");

    $players = Player::getPlayers();

    ?>

<div class="playerpage_content">
    <table class="players_table">
        <tr>
            <th> Name </th>
            <th> Team </th>
            <th> Joined </th>
        </tr>
    <?php
    foreach ($players as $key => $bzid) {
        $player = new Player($bzid);
        echo "<tr>";
        echo "<td><a href='" . $player->getURL() . "'>" . $player->getUsername() . "</a></td>";
        $teamlink = $player->getTeam()->getName();
        if ($player->getTeam()->isValid()) {
            $teamlink = '<a href="' . $player->getTeam()->getURL() . '">' . $teamlink . '</a>';
        }
        echo "<td>$teamlink</td>";
        echo "<td>" . $player->getJoinedDate() . "</td>";
        echo "</tr>";
    }
    ?>

    </table> <!-- end .players_table -->
</div> <!-- end .playerspage_content -->

<?php

}

$footer = new Footer();
$footer->draw();

?>
