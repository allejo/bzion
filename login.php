<?php

require_once("includes/checkToken.php");
require_once("bzion-load.php");

if (!isset($_GET["token"]) && !isset($_GET["username"])) {
    Header::go("home");
}

$token = $_GET["token"];
$username = $_GET["username"];

// Don't check whether IPs match if we're on a development environment
$checkIP = !DEVELOPMENT;
$info = validate_token($token, $username, array(), $checkIP);

if (isset($info)) {
    if(session_id() == '') {
        // Session hasn't started
        session_start();
    }

    $_SESSION['username'] = $info['username'];
    $_SESSION['groups'] = $info['groups'];

    $go = "home";

    if (!Player::playerBZIDExists($info['bzid'])) {
        $player = Player::newPlayer($info['bzid'], $info['username']);
        $go = "/profile"; // If they're new, redirect to their profile page so they can add some info
    } else {
        $player = Player::getFromBZID($info['bzid']);
    }

    $_SESSION['playerId'] = $player->getId();
    $player->updateLastLogin();

    Player::saveUsername($player->getId(), $info['username']);
    Visit::enterVisit($player->getId(), $_SERVER['REMOTE_ADDR'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER']);

    Header::go($go);

} else {
    echo "There was an error processing your login. Please go back and try again.";
}

?>
