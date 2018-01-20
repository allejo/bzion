<?php

use BZIon\Composer\ScriptHandler;

require_once dirname(__DIR__) . '/bzion-load.php';

$kernel = new AppKernel('test', true);
$kernel->boot();

function clearDatabase()
{
    $db = Database::getInstance();

    // Get an array of the tables in the database, so that we can remove them
    $tables = array_map(function ($val) { return current($val); },
        $db->query('SHOW TABLES'));

    if (count($tables) > 0) {
        $db->execute('SET foreign_key_checks = 0');
        $db->execute('DROP TABLES ' . implode($tables, ','));
        $db->execute('SET foreign_key_checks = 1');
    }

    ScriptHandler::migrateDatabase(null, true);

    if ($modelCache = Service::getModelCache()) {
        $modelCache->clear();
    }
}

clearDatabase();
