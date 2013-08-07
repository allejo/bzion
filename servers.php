<?php

include("bzion-load.php");

define("ROGUE", 0);
define("RED", 1);
define("GREEN", 2);
define("BLUE", 3);
define("PURPLE", 4);
define("OBSERVER", 5);

$header = new Header("Servers");
$header->draw();

$servers = Server::getServers();

foreach ($servers as $key => $id) {
    $server = new Server($id);

    if ($server->staleInfo()) {
        $server->forceUpdate();
    }

    echo "<strong>" . $server->getName() . "</strong><br />";
    echo "<em>" . $server->getAddress() . "</em><br />";

    echo "Total Players: " . $server->numPlayers();
    if ($server->numPlayers() > 0) {
        echo "<ul>";
        foreach($server->getPlayers() as $player) {
            echo "<li>" . $player['sign'];
            switch ($player['team']) {
                case ROGUE:
                    echo " (Rogue)";
                    break;
                case RED:
                    echo " (Red)";
                    break;
                case GREEN:
                    echo " (Green)";
                    break;
                case BLUE:
                    echo " (Blue)";
                    break;
                case PURPLE:
                    echo " (Purple)";
                    break;
                case OBSERVER:
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
