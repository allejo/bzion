<?php
require_once __DIR__ . '/bzion-load.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$kernel = new AppKernel(AppKernel::guessEnvironment(), DEVELOPMENT > 0);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

