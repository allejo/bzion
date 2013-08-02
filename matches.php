<?php

include("bzion-load.php");

$header = new Header();


if (isset($_GET['alias'])) {
    $team = Team::getFromAlias($_GET['alias']);
} else if (isset($_GET['id'])) {
    $team = new Team($_GET['id']);
}

if (isset($team)) {
    $header->draw("Matches :: " . $team->getName());

    $matches = $team->getMatches();

    echo "<br /><br />";

    foreach ($matches as $key => $id) {
        $match = new Match($id);
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

} else {

    $header->draw("Matches");

    $matches = Match::getMatches();

    echo "<br /><br />";

    foreach ($matches as $key => $id) {
        $match = new Match($id);
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

}

$footer = new Footer();
$footer->draw();

?>
