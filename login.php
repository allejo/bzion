<?php

require_once("checkToken.php");
require_once("bzion-load.php");

$token = $_GET["token"];
$username = $_GET["username"];

// Don't check whether IPs match if we're on a development environment
$checkIP = !DEVELOPMENT;
$info = validate_token($token, $username, array(), $checkIP);

if (isset($info)) {

    $go = "index.php";

    if (!Player::playerExists($info['bzid'])) {
        Player::newPlayer($info['bzid'], $info['username']);
        $go = "profile.php"; // If they're new, redirect to their profile page so they can add some info
    }

    session_start();

    $_SESSION['username'] = $info['username'];
    $_SESSION['bzid'] = $info['bzid'];
    $_SESSION['groups'] = $info['groups'];

    $player = new Player($info['bzid']);
    $player->updateLastLogin();
    Visit::enterVisit($info['bzid'], $_SERVER['REMOTE_ADDR'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER']);

    Header::go($go);

} else {
    echo "There was an error processing your login. Please go back and try again.";
}

?>
