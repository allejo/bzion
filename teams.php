<?php

include("bzion-load.php");

$header = new Header();


if (isset($_GET['alias'])) {
    $team = Team::getFromAlias($_GET['alias']);
} else if (isset($_GET['id'])) {
    $team = new Team($_GET['id']);
}

if (isset($team)) {
    $header->draw("Teams :: " . $team->getName());

    echo "<div class="team_name">" . $team->getName() . "</div>";
    echo "ELO: " . $team->getElo() . "<br />";
    echo "Matches: <a href='" . $team->getMatchesURL() . "'>" . $team->getNumTotalMatches() . "</a><br />";
    echo "Members: " . $team->getNumMembers() . "<br />";
    $leader = $team->getLeader();
    echo "Leader: " . $leader['username'] . "<br />";
    echo "Activity: " . $team->getActivity() . "<br />";
    echo "Created: " . $team->getCreationDate() . "<br />";
    echo "<br />";

} else {

    $header->draw("Teams");

    $teams = Team::getTeams();

    foreach ($teams as $key => $id) {
        $team = new Team($id);
        echo "<b><a href='" . $team->getURL() . "'>" . $team->getName() . "</a></b><br />";
        echo "ELO: " . $team->getElo() . "<br />";
        echo "Matches: <a href='" . $team->getMatchesURL() . "'>" . $team->getNumTotalMatches() . "</a><br />";
        echo "Members: " . $team->getNumMembers() . "<br />";
        $leader = $team->getLeader();
        echo "Leader: " . $leader['username'] . "<br />";
        echo "Activity: " . $team->getActivity() . "<br />";
        echo "Created: " . $team->getCreationDate() . "<br />";
        echo "<br />";
    }

}

$footer = new Footer();
$footer->draw();

?>
