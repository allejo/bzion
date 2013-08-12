<?php

include("bzion-load.php");

$header = new Header("Servers");
$header->draw();

$servers = Server::getServers();

foreach ($servers as $key => $id) {
    $server = new Server($id);

    if ($server->staleInfo()) {
        $server->forceUpdate();
        $header->go("/servers");
    }

    echo "<strong>" . $server->getName() . "</strong><br />";
    echo "<em>" . $server->getAddress() . "</em><br />";

    echo "Total Players: " . $server->numPlayers();
    if ($server->numPlayers() > 0) {
        echo "<ul>";
        foreach($server->getPlayers() as $player) {
            echo "<li>" . htmlspecialchars($player['sign']);
            switch ($player['team']) {
                case 0:
                    echo " (Rogue)";
                    break;
                case 1:
                    echo " (Red)";
                    break;
                case 2:
                    echo " (Green)";
                    break;
                case 3:
                    echo " (Blue)";
                    break;
                case 4:
                    echo " (Purple)";
                    break;
                case 5:
                    echo " (Observer)";
                    break;
                default:
                    break;
            }
        }
        echo "</ul>";
    }
    echo "<br /><small>Last update: " . $server->lastUpdate() . "</small>";
    echo "<br /><br />";
}

$footer = new Footer();
$footer->draw();

?>
