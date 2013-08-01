<?php

require_once("bzion-load.php");

// destroy the session and redirect to the previous page
// or to index.php if the page was loaded directly

$header = new Header();

session_destroy();

$loc = "/";
$use_own = false;

if (isset($_SERVER["HTTP_REFERER"])) {
    $loc = $_SERVER["HTTP_REFERER"];
    $use_own = true;
}

Header::go($loc, $use_own);

?>
