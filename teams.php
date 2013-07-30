<?php

include("bzion-load.php");

$header = new Header();
$header->draw("Teams");

$teams = Team::getTeams();

echo "<pre>";
print_r($teams);
echo "</pre>";

$footer = new Footer();
$footer->draw();

?>
