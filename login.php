<?php

require_once("checkToken.php");
require_once("bzion-load.php");

$token = $_GET["token"];
$username = $_GET["username"];

$info = validate_token($token, $username);

if (isset($info)) {

    if (!Player::playerExists($info['bzid'])) {
        Player::newPlayer($info['bzid'], $info['username']);
    }

    session_start();
    $_SESSION['username'] = $info['username'];
    $_SESSION['bzid'] = $info['bzid'];
    $_SESSION['groups'] = $info['groups'];

    Visit::enterVisit($info['bzid'], $_SERVER['REMOTE_ADDR'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER']);
    
    Header::go("index.php");

} else {
    echo "There was an error processing your login. Please go back and try again.";
}

?>