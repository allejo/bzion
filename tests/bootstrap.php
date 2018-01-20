<?php

require_once dirname(__DIR__) . '/bzion-load.php';

$kernel = new AppKernel('test', true);
$kernel->boot();

// @todo Extract clearDatabase() function to separate class
// @body The test database should be cleared every time the bootstrap is run, therefore this functionality should be in a more generic context
FeatureContext::clearDatabase();
