<?php

include("../bzion-load.php");

$header = new Header();

if (!isset($_SESSION['username'])) {
    die("You need to be logged in to do this.");
}

if (!isset($_GET['to']) || !isset($_GET['content'])) {
    die("Bad request");
}

$group_to = new Group($_GET['to']);
$content  = $_GET['content'];
$bzid     = $_SESSION['bzid'];

if (!$group_to->isValid()) {
    die("The group you specified does not exist");
}

if (!$group_to->isMember($bzid)) {
    die("You aren't a member of that group");
}

// TODO: Check if the user belongs in that group
Message::sendMessage($group_to->getId(), $_SESSION['bzid'], $content);