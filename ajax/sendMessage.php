<?php

include("../bzion-load.php");

$header = new Header();

if (!isset($_SESSION['username'])) {
    die("You need to be logged in to do this.");
}

if (!isset($_POST['to']) || !isset($_POST['content'])) {
    die("Bad request");
}

$group_to = $_POST['to'];
$content  = $_POST['content'];

// TODO: Check if the user belongs in that group
Message::sendMessage($group_to, $_SESSION['bzid'], $content);