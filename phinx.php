<?php

use BZIon\Composer\ScriptHandler;

require("vendor/autoload.php");

$config = ScriptHandler::getDatabaseConfig();
$testConfig = ScriptHandler::getDatabaseConfig(true);

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
            'pass' => $config['password']
        ),
        'test' => ($testConfig) ? array(
            'adapter' => 'mysql',
            'host' => $testConfig['host'],
            'name' => $testConfig['database'],
            'user' => $testConfig['username'],
            'pass' => $testConfig['password']
        ) : null
    )
);
