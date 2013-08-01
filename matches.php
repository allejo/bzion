<?php

include("bzion-load.php");

$header = new Header();

$header->draw("Matches");

$matches = Match::getMatches();

echo "<br /><br />";

foreach ($matches as $key => $value) {
    //$match = new Match($value['id']);
    $team_a = new Team($value['team_a']);
    $team_b = new Team($value['team_b']);
    echo "<b>" . $team_a->getName() . " (" . $value['team_a_points'] . " points) vs " . $team_b->getName() . " (" . $value['team_b_points'] . " points) </b><br />";
    echo "+/- " . $value['elo_diff'] . "<br />";
    echo $team_a->getName() . "'s new ELO: " . $value['team_a_elo_new'] . "<br />";
    echo $team_b->getName() . "'s new ELO: " . $value['team_b_elo_new'] . "<br />";
    echo "Duration: " . $value['duration'] . " min <br />";
    echo "Timestamp: " . $match->getTimestamp() . "<br />";
    echo "<br />";
}

$footer = new Footer();
$footer->draw();

?>
