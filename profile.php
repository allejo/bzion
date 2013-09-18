<?php

include("bzion-load.php");

$header = new Header();

if (!isset($_SESSION['username'])) {
    $header->go("home");
}

$header->draw("Profile");

$me = new Player($_SESSION['bzid']);

$playerTeam = $me->getTeam();
$teamlink = $playerTeam->getName();

if ($playerTeam->isValid()) {
    $teamlink = '<a href="' . $playerTeam->getURL() . '">' . $teamlink . '</a>';
}

echo "<h2>" . $me->getUsername() . "</h2><br />";
echo "Team: $teamlink<br />";
echo "Joined: " . $me->getJoinedDate() . "<br />";

echo "<br />More content coming soon...<br />";

echo "<br /><a href='#'>Edit your profile...</a>";

$footer = new Footer();
$footer->draw();

?>
