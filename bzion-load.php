<?php

if (!@include("bzion-config.php")) {
    header("Location: install.php");
    die();
}

require("classes/Autoloader.php");

foreach ($classesDir as $dir) {
    AutoLoader::registerDirectory($dir);
}

spl_autoload_register(array("AutoLoader", "loadClass"));

mb_internal_encoding("UTF-8");

$db = Database::getInstance();
