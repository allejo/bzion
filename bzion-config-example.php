<?php

/*
 * Set the credentials used for the MySQL database
 */
DEFINE("MYSQL_HOST", "localhost");
DEFINE("MYSQL_DB_NAME", "bzion");
DEFINE("MYSQL_USER", "bzion_admin");
DEFINE("MYSQL_PASSWORD", "password");

/*
 * An array of directories to be autoloaded in bzion-load.php
 */
$classesDir = array (
    "classes/"
);

/*
 * League specific settings
 */
DEFINE("DURATION", serialize(array(
	"20" => "(2/3)" // 20 minute match is 2/3rds of a normal match's elo
)));

/*
 * Miscellaneous settings
 */
DEFINE("LIST_SERVER", "http://my.bzflag.org/db/?action=LIST&version=BZFS0221"); // BZFlag List Server
DEFINE("UPDATE_INTERVAL", "5"); // Server polling interval in minutes
DEFINE("DOC_ROOT", dirname(__FILE__)); // The BZiON document root
DEFINE("HTTP_ROOT", $_SERVER["HTTP_HOST"]); // The root URL of the website
DEFINE("MYSQL_DEBUG", true);  // Whether or not to log MySQL errors
DEFINE("ERROR_LOG", DOC_ROOT . "/bzion_errors.log"); // The location where errors will be written