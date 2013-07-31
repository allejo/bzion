<?php

include("bzion-load.php");

$header = new Header();


if (isset($_GET['alias'])) {

    $id = Team::getIdFromAlias($_GET['alias']);
    $team = new Team($id['id']);

    $header->draw("Teams :: " . $team->getName());

    echo "<br /><br />";
    echo "<b>" . $team->getName() . "</b><br />";
    echo "ELO: " . $team->getElo() . "<br />";
    echo "Matches: " . $team->getNumTotalMatches() . "<br />";
    echo "Members: " . $team->getNumMembers() . "<br />";
    $leader = $team->getLeader();
    echo "Leader: " . $leader['username'] . "<br />";
    echo "Activity: " . $team->getActivity() . "<br />";
    echo "Created: " . $team->getCreationDate() . "<br />";
    echo "<br />";

} else if (isset($_GET['id'])) {

    $team = new Team($_GET['id']);

    $header->draw("Teams :: " . $team->getName());

    echo "<br /><br />";
    echo "<b>" . $team->getName() . "</b><br />";
    echo "ELO: " . $team->getElo() . "<br />";
    echo "Matches: " . $team->getNumTotalMatches() . "<br />";
    echo "Members: " . $team->getNumMembers() . "<br />";
    $leader = $team->getLeader();
    echo "Leader: " . $leader['username'] . "<br />";
    echo "Activity: " . $team->getActivity() . "<br />";
    echo "Created: " . $team->getCreationDate() . "<br />";
    echo "<br />";

} else {

    $header->draw("Teams");

    $teams = Team::getTeams();

    echo "<br /><br />";

    foreach ($teams as $key => $value) {
        $team = new Team($value['id']);
        echo "<b><a href='" . $team->getURL() . "'>" . $team->getName() . "</a></b><br />";
        echo "ELO: " . $team->getElo() . "<br />";
        echo "Matches: " . $team->getNumTotalMatches() . "<br />";
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
