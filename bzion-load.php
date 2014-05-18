<?php

if (!@include("bzion-config.php")) {
    define('NO_CONFIG', true);
}

require("vendor/autoload.php");

mb_internal_encoding("UTF-8");

$db = Database::getInstance();
