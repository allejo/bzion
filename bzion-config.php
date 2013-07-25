<?php
    
/*
 * Set the credentials used for the MySQL database
 */
DEFINE("MYSQL_HOST", "localhost");
DEFINE("MYSQL_DB_NAME", "bzion");
DEFINE("MYSQL_USER", "bzion_admin");
DEFINE("MYSQL_PASSWORD", "password");

/*
 * An array of directories to be autoloaded in bzion-config.php
 */
$classesDir = array (
    "classes/"
);

/*
 * Misc settings
 */
DEFINE("LIST_SERVER", "http://my.bzflag.org/db/?action=LIST&version=BZFS0221");