<?php

$header = new Header();


if (isset($_GET['alias'])) {
    $player = Player::getFromAlias($_GET['alias']);
} else if (isset($_GET['bzid'])) {
    $player = Player::getFromBZID($_GET['bzid']);
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

        echo "<br />", $player->getDescription() ,"<br />";
    } else {
        $header->draw("Players");

        echo "The specified player could not be found. <br />";
    }

} else {
    $header->draw("Players");

    $players = Player::getPlayers();

    ?>

<div class="table players">
    <ul>
        <li>Name</li>
        <li>Team</li>
        <li>Joined</li>
    </ul>

    <?php
        foreach ($players as $player)
        {
            echo '<ul>';
            echo '    <li><a href="' . $player->getURL() . '">' . $player->getUsername() . '</a></li>';
            $teamlink = $player->getTeam()->getName();
            if ($player->getTeam()->isValid()) {
                $teamlink = '<a href="' . $player->getTeam()->getURL() . '">' . $teamlink . '</a>';
            }
            echo '    <li>' . $teamlink . '</li>';
            echo '    <li>' . $player->getJoinedDate() . '</li>';
            echo '</ul>';
        }
    ?>
</div>

<?php

}

$footer = new Footer();
$footer->draw();

?>
