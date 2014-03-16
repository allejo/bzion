<?php

if (!@include("bzion-config.php")) {
    header("Location: install.php");
    die();
}

require("vendor/autoload.php");

mb_internal_encoding("UTF-8");

$db = Database::getInstance();
