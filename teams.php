<?php

include("bzion-load.php");

$header = new Header();
$header->draw("Teams");

$teams = Team::getTeams();

echo "<pre>";
print_r($teams);
echo "</pre>";

foreach ($teams as $key => $value) {
	$team = new Team($value['id']);
	echo "<b>" . $team->getName() . "</b><br />";
	echo "ELO: " . $team->getElo() . "<br />";
	echo "Matches: " . $team->getNumTotalMatches() . "<br />";
	echo "Members: " . $team->getNumMembers() . "<br />";
	$leader = $team->getLeader();
	echo "Leader: " . $leader['username'] . "<br />";
	echo "Activity: " . $team->getActivity() . "<br />";
	echo "Created: " . $team->getCreationDate() . "<br />";
	echo "<br />";
}

$footer = new Footer();
$footer->draw();

?>
