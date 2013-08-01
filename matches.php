<?php

include("bzion-load.php");

$header = new Header();

$header->draw("Matches");

$matches = Match::getMatches();

echo "<br /><br />";

foreach ($matches as $key => $value) {
    $match = new Match($value['id']);
    $team_a = new Team($match->getTeamA());
    $team_b = new Team($match->getTeamB());
    echo "<b>" . $team_a->getName() . " (" . $match->getTeamAPoints() . " points) vs " . $team_b->getName() . " (" . $match->getTeamBPoints() . " points) </b><br />";
    echo "+/- " . $match->getEloDiff() . "<br />";
    echo $team_a->getName() . "'s new ELO: " . $match->getTeamAEloNew() . "<br />";
    echo $team_b->getName() . "'s new ELO: " . $match->getTeamBEloNew() . "<br />";
    echo "Duration: " . $match->getDuration() . " min <br />";
    echo "Timestamp: " . $match->getTimestamp() . "<br />";
    echo "<br />";
}

$footer = new Footer();
$footer->draw();

?>
