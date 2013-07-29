<?php

require_once("checkToken.php");
require_once("bzion-load.php");

$token = $_GET["token"];
$username = $_GET["username"];

$info = validate_token($token, $username);

if (isset($info)) {
    session_start();
    $_SESSION['username'] = $info['username'];
    $_SESSION['bzid'] = $info['bzid'];
    $_SESSION['groups'] = $info['groups'];
    
    Header::go("index.php");

} else {
    echo "There was an error processing your login. Please go back and try again.";
}

?>