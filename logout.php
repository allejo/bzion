<?php

require_once("bzion-load.php");

// destroy the session and redirect to the previous page
// or to index.php if the page was loaded directly

$header = new Header();

session_destroy();

$loc = "index.php";

if (isset($_SERVER["HTTP_REFERER"])) {
    $loc = $_SERVER["HTTP_REFERER"];
}

Header::go($loc);

?>
