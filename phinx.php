<?php

use BZIon\Composer\ScriptHandler;
use Symfony\Component\Yaml\Yaml;

require("vendor/autoload.php");

$config = ScriptHandler::getDatabaseConfig();

return array(
    'paths' => array(
        'migrations' => __DIR__ . '/migrations'
    ),
    'environments' => array(
        'default_migration_table' => 'migration_log',
        'default_database' => 'main',
        'main' => array(
            'adapter' => 'mysql',
            'host' => $config['host'],
            'name' => $config['database'],
            'user' => $config['username'],
            'pass' => $config['password'],
            // 'port' => 3306
        )
    )
);
