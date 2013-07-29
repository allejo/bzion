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