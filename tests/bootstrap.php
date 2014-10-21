<?php

require_once(dirname(__DIR__) . '/bzion-load.php');

define('DEVELOPMENT', true);

$kernel = new AppKernel('test', true);
$kernel->boot();

