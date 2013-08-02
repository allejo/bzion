<?php

/*
 * MySQL settings
 */
DEFINE("MYSQL_HOST", "localhost"); // Database host
DEFINE("MYSQL_DB_NAME", "bzion"); // Database name
DEFINE("MYSQL_USER", "bzion_admin"); // Database username
DEFINE("MYSQL_PASSWORD", "password"); // Database password
DEFINE("MYSQL_DEBUG", TRUE);  // Whether or not to log MySQL errors on a file

/*
 * Site settings
 */
DEFINE("SITE_TITLE", "BZiON: A League Management System");

/*
 * Directories to be autoloaded in bzion-load.php
 */
$classesDir = array (
    "classes/"
);

/*
 * League specific settings
 */
DEFINE("DURATION", serialize(array(
	"20" => "(2/3)" // 20 minute match is 2/3rds of a normal match's ELO
)));

/*
 * File, directory and URL settings
 */
DEFINE("DOC_ROOT", dirname(__FILE__)); // The BZiON document root
DEFINE("HTTP_ROOT", $_SERVER["HTTP_HOST"]); // The root URL of the website
DEFINE("ERROR_LOG", DOC_ROOT . "/bzion_errors.log"); // The location where errors will be written

/*
 * Miscellaneous settings
 */
DEFINE("LIST_SERVER", "http://my.bzflag.org/db/?action=LIST&version=BZFS0221"); // BZFlag List Server
DEFINE("UPDATE_INTERVAL", "5"); // Server polling interval in minutes
DEFINE("DEVELOPMENT", FALSE); // Whether to enable some functions which make debugging easier
                              // WARNING: Setting this to TRUE might introduce significant security risks
                              // and should NOT be used in a production environment
DEFINE("DATE_FORMAT", "Y-m-d H:i:s"); // Default date format. Default results in: YYYY-MM-DD HH:MM:SS
